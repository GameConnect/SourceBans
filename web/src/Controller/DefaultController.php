<?php

namespace SourceBans\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\Entity\Server;
use SourceBans\Specification\Server\IsEnabled;
use SourceBans\Specification\ServerSpecification;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class DefaultController
{
    /** @var EngineInterface */
    private $templating;

    /** @var EntityRepository */
    private $serverRepository;

    public function __construct(
        EngineInterface $templating,
        EntityManagerInterface $entityManager
    ) {
        $this->templating = $templating;
        $this->serverRepository = $entityManager->getRepository(Server::class);
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

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->templating->renderResponse('login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }
}
