<?php

namespace SourceBans\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SourceBans\CoreBundle\Adapter\ServerAdapter;
use SourceBans\CoreBundle\Entity\Server;
use SourceBans\CoreBundle\Util\SourceQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * DefaultController
 *
 * @Route(service="sourcebans.core.controller.servers")
 */
class ServersController
{
    const QUERY_INFO    = 1;
    const QUERY_PLAYERS = 2;
    const QUERY_RULES   = 4;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ServerAdapter
     */
    private $adapter;

    /**
     * @var string
     */
    private $imageDir;

    /**
     * @param TranslatorInterface $translator
     * @param ServerAdapter       $adapter
     * @param string              $imageDir
     */
    public function __construct(TranslatorInterface $translator, ServerAdapter $adapter, $imageDir)
    {
        $this->translator = $translator;
        $this->adapter = $adapter;
        $this->imageDir = $imageDir;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/servers/query/{id}")
     */
    public function queryAction(Request $request, Server $server)
    {
        return new JsonResponse($this->query($server, self::QUERY_INFO|self::QUERY_PLAYERS));
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/servers/info/{id}")
     */
    public function infoAction(Request $request, Server $server)
    {
        return new JsonResponse($this->query($server, self::QUERY_INFO));
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/servers/players/{id}")
     */
    public function playersAction(Request $request, Server $server)
    {
        return new JsonResponse($this->query($server, self::QUERY_PLAYERS));
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/servers/rules/{id}")
     */
    public function rulesAction(Request $request, Server $server)
    {
        return new JsonResponse($this->query($server, self::QUERY_RULES));
    }

    /**
     * @param Server $server
     * @param integer $queries
     * @return array
     */
    private function query(Server $server, $queries)
    {
        $query  = new SourceQuery($server->getHost(), $server->getPort());
        $result = [
            'id'   => $server->getId(),
            'host' => $server->getHost(),
            'port' => $server->getPort(),
        ];

        if ($queries & self::QUERY_INFO) {
            $info = $query->getInfo();
            if (empty($info)) {
                $result['error'] = [
                    'code'    => 'ERR_TIMEOUT',
                    'message' => $this->translator->trans('components.SourceQuery.err_timeout') . ' (' . $server . ')',
                ];
            } elseif ($info['hostname'] == "anned by server\n") {
                $result['error'] = [
                    'code'    => 'ERR_BLOCKED',
                    'message' => $this->translator->trans('components.SourceQuery.err_blocked') . ' (' . $server . ')',
                ];
            } else {
                $mapImage = '/maps/' . $server->getGame()->getFolder() . '/' . $info['map'] . '.jpg';

                $result['hostname']   = preg_replace('/[\x00-\x1F]/', null, $info['hostname']); // Strip UTF-8 characters
                $result['numplayers'] = $info['numplayers'];
                $result['maxplayers'] = $info['maxplayers'];
                $result['map']        = basename($info['map']); // Strip Steam Workshop folder
                $result['os']         = $info['os'];
                $result['secure']     = $info['secure'];
                $result['map_image']  = file_exists($this->imageDir . $mapImage) ? '/images' . $mapImage : null;
            }
        }
        if ($queries & self::QUERY_PLAYERS) {
            $result['players'] = $query->getPlayers();

            // Sort descending by score
            usort($result['players'], function ($playerA, $playerB) {
                if ($playerA['score'] != $playerB['score']) {
                    return $playerA['score'] > $playerB['score'] ? -1 : 1;
                }

                return strcasecmp($playerA['name'], $playerB['name']);
            });

            // Round connection time to minutes
            array_walk($result['players'], function ($player) {
                $player['time'] /= 60;
            });
        }
        if ($queries & self::QUERY_RULES) {
            $result['rules'] = $query->getRules();
        }

        return $result;
    }
}
