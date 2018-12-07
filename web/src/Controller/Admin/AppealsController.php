<?php

namespace SourceBans\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\Command\ArchiveAppeal;
use SourceBans\Entity\Appeal;
use SourceBans\Specification\AppealSpecification;
use SourceBans\Specification\IsArchived;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;

class AppealsController
{
    /** @var EngineInterface */
    private $templating;

    /** @var EntityRepository */
    private $repository;

    /** @var MessageBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        EngineInterface $templating,
        EntityManagerInterface $entityManager,
        MessageBusInterface $commandBus,
        RouterInterface $router
    ) {
        $this->templating = $templating;
        $this->repository = $entityManager->getRepository(Appeal::class);
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    public function index(string $type): Response
    {
        $archived = new IsArchived();
        if ($type != 'archive') {
            $archived = new Logic\Not($archived);
        }

        $specification = new Logic\AndX(
            new AppealSpecification(),
            $archived,
            new Query\OrderBy('createTime', Query\OrderBy::DESC)
        );
        $query = $this->repository->match($specification);

        return $this->templating->renderResponse('admin/appeals/index.html.twig', [
            'appeals' => $query->getResult(),
        ]);
    }

    public function archive(Appeal $appeal): Response
    {
        $this->commandBus->dispatch(new ArchiveAppeal($appeal));

        return new RedirectResponse($this->router->generate('admin_appeals_index'));
    }
}
