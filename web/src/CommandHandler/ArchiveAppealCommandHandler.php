<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\ArchiveAppeal;

class ArchiveAppealCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(ArchiveAppeal $command)
    {
        $appeal = $command->getAppeal();

        $appeal->setArchived(true);

        $this->entityManager->persist($appeal);
        $this->entityManager->flush();
    }
}
