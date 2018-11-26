<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\CreateAppeal;

class CreateAppealCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(CreateAppeal $command)
    {
        $appeal = $command->getAppeal();

        $this->entityManager->persist($appeal);
        $this->entityManager->flush();
    }
}
