<?php

namespace SourceBans\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\Command\CreateAppeal;
use SourceBans\Command\CreateReport;
use SourceBans\Entity\Appeal;
use SourceBans\Entity\Report;
use SourceBans\Entity\Server;
use SourceBans\Form\AppealForm;
use SourceBans\Form\ReportForm;
use SourceBans\Specification\Server\IsEnabled;
use SourceBans\Specification\ServerSpecification;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class DefaultController
{
    /** @var EngineInterface */
    private $templating;

    /** @var EntityRepository */
    private $serverRepository;

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
        $this->serverRepository = $entityManager->getRepository(Server::class);
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    public function index(): Response
    {
        $specification = new Logic\AndX(
            new ServerSpecification(),
            new IsEnabled(),
            new Query\OrderBy('name', null, 'game'),
            new Query\OrderBy('host'),
            new Query\OrderBy('port')
        );
        $query = $this->serverRepository->match($specification);

        return $this->templating->renderResponse('index.html.twig', [
            'servers' => $query->getResult(),
        ]);
    }

    public function appeal(Request $request): Response
    {
        $appeal = new Appeal();
        $appeal->setUserIp($request->getClientIp());

        $form = $this->formFactory->create(AppealForm::class, $appeal)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new CreateAppeal($appeal));

            return new RedirectResponse($this->router->generate('index'));
        }

        return $this->templating->renderResponse('appeal.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function report(Request $request): Response
    {
        $report = new Report();
        $report->setUserIp($request->getClientIp());

        $form = $this->formFactory->create(ReportForm::class, $report)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new CreateReport($report));

            return new RedirectResponse($this->router->generate('index'));
        }

        return $this->templating->renderResponse('report.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->templating->renderResponse('login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }
}
