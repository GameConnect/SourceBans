<?php

namespace SourceBans\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\Entity\Admin;
use SourceBans\Specification\AdminSpecification;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminsController
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
        $this->repository = $entityManager->getRepository(Admin::class);
    }

    public function index(): Response
    {
        $specification = new Logic\AndX(
            new AdminSpecification(),
            new Query\OrderBy('name')
        );
        $query = $this->repository->match($specification);

        return $this->templating->renderResponse('admin/admins/index.html.twig', [
            'admins' => $query->getResult(),
        ]);
    }
}
