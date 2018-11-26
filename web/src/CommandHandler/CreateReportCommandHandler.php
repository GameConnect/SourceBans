<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\CreateReport;

class CreateReportCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(CreateReport $command)
    {
        $report = $command->getReport();

        $this->entityManager->persist($report);
        $this->entityManager->flush();
    }
}
