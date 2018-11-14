<?php

namespace SourceBans\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Query;
use SourceBans\Entity\Game;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class GamesController
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
        $this->repository = $entityManager->getRepository(Game::class);
    }

    public function index(): Response
    {
        $specification = new Query\OrderBy('name');
        $query = $this->repository->match($specification);

        return $this->templating->renderResponse('admin/games/index.html.twig', [
            'games' => $query->getResult(),
        ]);
    }
}
