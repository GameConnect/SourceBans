<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\DeleteGroup;

class DeleteGroupCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(DeleteGroup $command)
    {
        $group = $command->getGroup();

        $this->entityManager->remove($group);
        $this->entityManager->flush();
    }
}
