<?php

namespace SourceBans\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\ServerAdapter;
use SourceBans\CoreBundle\Entity\Server;
use SourceBans\CoreBundle\Entity\SettingRepository;
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
     * @var SettingRepository
     */
    private $settings;

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
     * @param SettingRepository   $settings
     * @param ServerAdapter       $adapter
     * @param string              $imageDir
     */
    public function __construct(
        TranslatorInterface $translator,
        SettingRepository $settings,
        ServerAdapter $adapter,
        $imageDir
    ) {
        $this->translator = $translator;
        $this->settings = $settings;
        $this->adapter = $adapter;
        $this->imageDir = $imageDir;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/servers")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $servers = $this->adapter->all(
            $this->settings->get('items_per_page'),
            $request->query->getInt('page', 1),
            $request->query->get('sort'),
            $request->query->get('order')
        );

        return ['servers' => $servers];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/servers/query/{id}")
     */
    public function queryAction(Request $request, Server $server)
    {
        try {
            return new JsonResponse($this->query($server, self::QUERY_INFO|self::QUERY_PLAYERS));
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/servers/info/{id}")
     */
    public function infoAction(Request $request, Server $server)
    {
        try {
            return new JsonResponse($this->query($server, self::QUERY_INFO));
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e->getCode());
        }
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
     * @throws \RuntimeException
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
                throw new \RuntimeException(
                    $this->translator->trans('components.SourceQuery.err_timeout') . ' (' . $server . ')',
                    Response::HTTP_GATEWAY_TIMEOUT
                );
            }
            if ($info['hostname'] == "anned by server\n") {
                throw new \RuntimeException(
                    $this->translator->trans('components.SourceQuery.err_blocked') . ' (' . $server . ')',
                    Response::HTTP_FORBIDDEN
                );
            }

            $mapImage = '/maps/' . $server->getGame()->getFolder() . '/' . $info['map'] . '.jpg';

            $result['hostname']   = preg_replace('/[\x00-\x1F]/', null, $info['hostname']); // Strip UTF-8 characters
            $result['numplayers'] = $info['numplayers'];
            $result['maxplayers'] = $info['maxplayers'];
            $result['map']        = basename($info['map']); // Strip Steam Workshop folder
            $result['os']         = $info['os'];
            $result['secure']     = $info['secure'];
            $result['map_image']  = file_exists($this->imageDir . $mapImage) ? '/images' . $mapImage : null;
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
