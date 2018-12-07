<?php

namespace SourceBans\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Query;
use SourceBans\Command\CreateGroup;
use SourceBans\Command\DeleteGroup;
use SourceBans\Command\UpdateGroup;
use SourceBans\Entity\Group;
use SourceBans\Form\GroupForm;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;

class GroupsController
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
        $this->repository = $entityManager->getRepository(Group::class);
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    public function index(): Response
    {
        $specification = new Query\OrderBy('name');
        $query = $this->repository->match($specification);

        return $this->templating->renderResponse('admin/groups/index.html.twig', [
            'groups' => $query->getResult(),
        ]);
    }

    public function add(Request $request): Response
    {
        $group = new Group();
        $form = $this->formFactory->create(GroupForm::class, $group)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new CreateGroup($group));

            return new RedirectResponse($this->router->generate('admin_groups_index'));
        }

        return $this->templating->renderResponse('admin/groups/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function edit(Request $request, Group $group): Response
    {
        $form = $this->formFactory->create(GroupForm::class, $group)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new UpdateGroup($group));

            return new RedirectResponse($this->router->generate('admin_groups_index'));
        }

        return $this->templating->renderResponse('admin/groups/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function delete(Group $group): Response
    {
        $this->commandBus->dispatch(new DeleteGroup($group));

        return new RedirectResponse($this->router->generate('admin_groups_index'));
    }
}
