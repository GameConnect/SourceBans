<?php

namespace SourceBans\Controller\Admin;

use SourceBans\Command\CreateBan;
use SourceBans\Command\DeleteBan;
use SourceBans\Command\UnbanBan;
use SourceBans\Command\UpdateBan;
use SourceBans\Entity\Ban;
use SourceBans\Form\BanForm;
use SourceBans\Form\UnbanForm;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

class BansController
{
    /** @var EngineInterface */
    private $templating;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var MessageBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var Security */
    private $security;

    public function __construct(
        EngineInterface $templating,
        FormFactoryInterface $formFactory,
        MessageBusInterface $commandBus,
        RouterInterface $router,
        Security $security
    ) {
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->security = $security;
    }

    public function add(Request $request): Response
    {
        $ban = new Ban();
        $ban->setAdmin($this->security->getUser());
        $ban->setAdminIp($request->getClientIp());

        $form = $this->formFactory->create(BanForm::class, $ban)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new CreateBan($ban));

            return new RedirectResponse($this->router->generate('index'));
        }

        return $this->templating->renderResponse('admin/bans/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function edit(Request $request, Ban $ban): Response
    {
        $form = $this->formFactory->create(BanForm::class, $ban)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new UpdateBan($ban));

            return new RedirectResponse($this->router->generate('index'));
        }

        return $this->templating->renderResponse('admin/bans/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function unban(Request $request, Ban $ban): Response
    {
        $form = $this->formFactory->create(UnbanForm::class, $ban)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $unbanAdmin = $this->security->getUser();

            $this->commandBus->dispatch(new UnbanBan($ban, $unbanAdmin));

            return new RedirectResponse($this->router->generate('index'));
        }

        return $this->templating->renderResponse('admin/bans/unban.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function delete(Ban $ban): Response
    {
        $this->commandBus->dispatch(new DeleteBan($ban));

        return new RedirectResponse($this->router->generate('index'));
    }
}
