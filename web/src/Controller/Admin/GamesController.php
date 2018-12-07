<?php

namespace SourceBans\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Query;
use SourceBans\Command\CreateGame;
use SourceBans\Command\DeleteGame;
use SourceBans\Command\UpdateGame;
use SourceBans\Entity\Game;
use SourceBans\Form\GameForm;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;

class GamesController
{
    /** @var EngineInterface */
    private $templating;

    /** @var EntityRepository */
    private $repository;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var MessageBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var string */
    private $imagesDir;

    public function __construct(
        EngineInterface $templating,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        MessageBusInterface $commandBus,
        RouterInterface $router,
        string $imagesDir
    ) {
        $this->templating = $templating;
        $this->repository = $entityManager->getRepository(Game::class);
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->imagesDir = $imagesDir;
    }

    public function index(): Response
    {
        $specification = new Query\OrderBy('name');
        $query = $this->repository->match($specification);

        return $this->templating->renderResponse('admin/games/index.html.twig', [
            'games' => $query->getResult(),
        ]);
    }

    public function add(Request $request): Response
    {
        $game = new Game();
        $form = $this->formFactory->create(GameForm::class, $game)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new CreateGame($game));

            return new RedirectResponse($this->router->generate('admin_games_index'));
        }

        return $this->templating->renderResponse('admin/games/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function edit(Request $request, Game $game): Response
    {
        $game->setIcon(new File($this->imagesDir.'/'.$game->getIcon()));

        $form = $this->formFactory->create(GameForm::class, $game)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new UpdateGame($game));

            return new RedirectResponse($this->router->generate('admin_games_index'));
        }

        return $this->templating->renderResponse('admin/games/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function delete(Game $game): Response
    {
        $this->commandBus->dispatch(new DeleteGame($game));

        return new RedirectResponse($this->router->generate('admin_games_index'));
    }
}
