<?php

namespace SourceBans\CoreBundle\Controller;

use Rb\Specification\Doctrine\Condition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SourceBans\CoreBundle\Adapter\BanAdapter;
use SourceBans\CoreBundle\Entity\Ban;
use SourceBans\CoreBundle\Entity\SettingRepository;
use SourceBans\CoreBundle\Specification\Ban as BanSpecification;
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
     * @var SettingRepository
     */
    private $settings;

    /**
     * @var BanAdapter
     */
    private $adapter;

    /**
     * @param SettingRepository $settings
     * @param BanAdapter        $adapter
     */
    public function __construct(SettingRepository $settings, BanAdapter $adapter)
    {
        $this->settings = $settings;
        $this->adapter = $adapter;
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/bans")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $criteria = [];
        if ($request->query->get('type') == 'active') {
            $criteria[] = new BanSpecification\IsActive;
        }

        $bans = $this->adapter->all(
            $this->settings->get('items_per_page'),
            $request->query->getInt('page', 1),
            $request->query->get('sort'),
            $request->query->get('order'),
            $criteria
        );

        return ['bans' => $bans];
    }

    /**
     * @param Request $request
     * @return array|Response
     *
     * @Route("/bans/export/{type}")
     */
    public function exportAction(Request $request)
    {
        $type = ($request->query->get('type') == 'ip' ? Ban::TYPE_IP : Ban::TYPE_STEAM);
        /** @var Ban[] $bans */
        $bans = $this->adapter->allBy([
            new Condition\Equals('type', $type),
            new BanSpecification\IsPermanent,
        ]);

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
