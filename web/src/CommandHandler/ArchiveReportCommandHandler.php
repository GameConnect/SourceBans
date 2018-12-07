<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\ArchiveReport;

class ArchiveReportCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(ArchiveReport $command)
    {
        $report = $command->getReport();

        $report->setArchived(true);

        $this->entityManager->persist($report);
        $this->entityManager->flush();
    }
}
