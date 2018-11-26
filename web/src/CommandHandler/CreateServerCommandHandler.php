<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\CreateServer;

class CreateServerCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(CreateServer $command)
    {
        $server = $command->getServer();

        $this->entityManager->persist($server);
        $this->entityManager->flush();
    }
}