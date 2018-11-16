<?php

namespace SourceBans\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Query;
use SourceBans\Command\CreateServerGroup;
use SourceBans\Command\DeleteServerGroup;
use SourceBans\Command\UpdateServerGroup;
use SourceBans\Entity\ServerGroup;
use SourceBans\Form\ServerGroupForm;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;

class ServerGroupsController
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
        $this->repository = $entityManager->getRepository(ServerGroup::class);
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    public function index(): Response
    {
        $specification = new Query\OrderBy('name');
        $query = $this->repository->match($specification);

        return $this->templating->renderResponse('admin/server-groups/index.html.twig', [
            'serverGroups' => $query->getResult(),
        ]);
    }

    public function add(Request $request): Response
    {
        $serverGroup = new ServerGroup();
        $form = $this->formFactory->create(ServerGroupForm::class, $serverGroup)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new CreateServerGroup($serverGroup));

            return new RedirectResponse($this->router->generate('admin_server_groups_index'));
        }

        return $this->templating->renderResponse('admin/server-groups/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $serverGroup = $this->repository->find($id);
        $form = $this->formFactory->create(ServerGroupForm::class, $serverGroup)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new UpdateServerGroup($serverGroup));

            return new RedirectResponse($this->router->generate('admin_server_groups_index'));
        }

        return $this->templating->renderResponse('admin/server-groups/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function delete(int $id): Response
    {
        $this->commandBus->dispatch(new DeleteServerGroup($id));

        return new RedirectResponse($this->router->generate('admin_server_groups_index'));
    }
}
