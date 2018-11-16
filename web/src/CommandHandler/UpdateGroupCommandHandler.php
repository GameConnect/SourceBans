<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\UpdateGroup;

class UpdateGroupCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(UpdateGroup $command)
    {
        $group = $command->getGroup();

        $this->entityManager->persist($group);
        $this->entityManager->flush();
    }
}
