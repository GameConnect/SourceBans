<?php

namespace SourceBans\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SourceBans\CoreBundle\Adapter\BanAdapter;
use SourceBans\CoreBundle\Entity\Ban;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * DefaultController
 *
 * @Route(service="sourcebans.core.controller.bans")
 */
class BansController
{
    /**
     * @var BanAdapter
     */
    private $adapter;

    /**
     * @param BanAdapter $adapter
     */
    public function __construct(BanAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/bans/export/{type}")
     */
    public function exportAction(Request $request)
    {
        $type = $request->query->get('type') == 'ip' ? Ban::TYPE_IP : Ban::TYPE_STEAM;
        /** @var Ban[] $bans */
        $bans = $this->adapter->allByType($type, null, null, ['permanent' => true]);

        $content = '';
        foreach ($bans as $ban) {
            $content .= ($type == Ban::TYPE_IP)
                ? 'banip 0 ' . $ban->getIp() . "\n"
                : 'banid 0 ' . $ban->getSteam() . "\n";
        }

        $response = new Response($content);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $type == Ban::TYPE_IP ? 'banned_ip.cfg' : 'banned_user.cfg'
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }
}
