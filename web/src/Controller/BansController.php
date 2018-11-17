<?php

namespace SourceBans\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\Entity\Ban;
use SourceBans\Specification\BanSpecification;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

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
}
