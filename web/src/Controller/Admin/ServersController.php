<?php

namespace SourceBans\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\Entity\Server;
use SourceBans\Specification\ServerSpecification;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class ServersController
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
        $this->repository = $entityManager->getRepository(Server::class);
    }

    public function index(): Response
    {
        $specification = new Logic\AndX(
            new ServerSpecification(),
            new Query\OrderBy('name', null, 'game'),
            new Query\OrderBy('host'),
            new Query\OrderBy('port')
        );
        $query = $this->repository->match($specification);

        return $this->templating->renderResponse('admin/servers/index.html.twig', [
            'servers' => $query->getResult(),
        ]);
    }
}
