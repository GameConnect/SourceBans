<?php

namespace SourceBans\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\Command\CreateAdmin;
use SourceBans\Command\DeleteAdmin;
use SourceBans\Command\UpdateAdmin;
use SourceBans\Entity\Admin;
use SourceBans\Form\AdminForm;
use SourceBans\Specification\AdminSpecification;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;

class AdminsController
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
        $this->repository = $entityManager->getRepository(Admin::class);
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
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

    public function add(Request $request): Response
    {
        $admin = new Admin();
        $form = $this->formFactory->create(AdminForm::class, $admin)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new CreateAdmin($admin));

            return new RedirectResponse($this->router->generate('admin_admins_index'));
        }

        return $this->templating->renderResponse('admin/admins/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $admin = $this->repository->find($id);
        $form = $this->formFactory->create(AdminForm::class, $admin)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new UpdateAdmin($admin));

            return new RedirectResponse($this->router->generate('admin_admins_index'));
        }

        return $this->templating->renderResponse('admin/admins/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function delete(int $id): Response
    {
        $this->commandBus->dispatch(new DeleteAdmin($id));

        return new RedirectResponse($this->router->generate('admin_admins_index'));
    }
}
