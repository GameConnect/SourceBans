<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SourceBans\Command\ArchiveReport;
use SourceBans\Entity\Report;

class ArchiveReportCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var EntityRepository */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Report::class);
    }

    public function __invoke(ArchiveReport $command)
    {
        $id = $command->getId();
        $report = $this->repository->find($id);

        if (!$report) {
            throw new \InvalidArgumentException(sprintf('The report with ID %d was not found.', $id));
        }

        $report->setArchived(true);

        $this->entityManager->persist($report);
        $this->entityManager->flush();
    }
}
