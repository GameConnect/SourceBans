<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\UpdateServerGroup;

class UpdateServerGroupCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(UpdateServerGroup $command)
    {
        $serverGroup = $command->getServerGroup();

        $this->entityManager->persist($serverGroup);
        $this->entityManager->flush();
    }
}
