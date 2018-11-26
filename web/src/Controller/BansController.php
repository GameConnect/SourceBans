<?php

namespace SourceBans\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Condition;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\Entity\Ban;
use SourceBans\Specification\Ban\IsPermanent;
use SourceBans\Specification\BanSpecification;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class BansController
{
    /** @var EngineInterface */
    private $templating;

    /** @var EntityRepository */
    private $repository;

    public function __construct(
        EngineInterface $templating,
        EntityManagerInterface $entityManager
    ) {
        $this->templating = $templating;
        $this->repository = $entityManager->getRepository(Ban::class);
    }

    public function index(): Response
    {
        $specification = new Logic\AndX(
            new BanSpecification(),
            new Query\OrderBy('createTime', Query\OrderBy::DESC)
        );
        $query = $this->repository->match($specification);

        return $this->templating->renderResponse('bans/index.html.twig', [
            'bans' => $query->getResult(),
        ]);
    }

    public function export(string $type): Response
    {
        $banType = ($type == 'ip' ? Ban::TYPE_IP : Ban::TYPE_STEAM);

        $specification = new Logic\AndX(
            new BanSpecification(),
            new IsPermanent(),
            new Condition\Equals('type', $banType)
        );
        $query = $this->repository->match($specification);
        /** @var Ban[] $bans */
        $bans = $query->getResult();

        $content = '';
        foreach ($bans as $ban) {
            $content .= ($banType == Ban::TYPE_IP)
                ? sprintf("banip %d %s\n", Ban::LENGTH_PERMANENT, $ban->getIp())
                : sprintf("banid %d %s\n", Ban::LENGTH_PERMANENT, $ban->getSteam());
        }

        $response = new Response($content);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $banType == Ban::TYPE_IP ? 'banned_ip.cfg' : 'banned_user.cfg'
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }
}
