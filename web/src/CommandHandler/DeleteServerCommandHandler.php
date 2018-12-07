<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\DeleteServer;

class DeleteServerCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(DeleteServer $command)
    {
        $server = $command->getServer();

        $this->entityManager->remove($server);
        $this->entityManager->flush();
    }
}
