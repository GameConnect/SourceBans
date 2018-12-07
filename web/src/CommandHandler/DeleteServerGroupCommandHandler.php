<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\DeleteServerGroup;

class DeleteServerGroupCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(DeleteServerGroup $command)
    {
        $serverGroup = $command->getServerGroup();

        $this->entityManager->remove($serverGroup);
        $this->entityManager->flush();
    }
}
