<?php

namespace SourceBans\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\Command\CreateServer;
use SourceBans\Command\DeleteServer;
use SourceBans\Command\UpdateServer;
use SourceBans\Entity\Server;
use SourceBans\Form\ServerForm;
use SourceBans\Specification\ServerSpecification;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;

class ServersController
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

    public function __construct(
        EngineInterface $templating,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        MessageBusInterface $commandBus,
        RouterInterface $router
    ) {
        $this->templating = $templating;
        $this->repository = $entityManager->getRepository(Server::class);
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
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

    public function add(Request $request): Response
    {
        $server = new Server();
        $form = $this->formFactory->create(ServerForm::class, $server)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new CreateServer($server));

            return new RedirectResponse($this->router->generate('admin_servers_index'));
        }

        return $this->templating->renderResponse('admin/servers/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $server = $this->repository->find($id);
        $form = $this->formFactory->create(ServerForm::class, $server)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new UpdateServer($server));

            return new RedirectResponse($this->router->generate('admin_servers_index'));
        }

        return $this->templating->renderResponse('admin/servers/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function delete(int $id): Response
    {
        $this->commandBus->dispatch(new DeleteServer($id));

        return new RedirectResponse($this->router->generate('admin_servers_index'));
    }
}
