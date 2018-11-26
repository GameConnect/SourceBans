<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SourceBans\Command\ArchiveAppeal;
use SourceBans\Entity\Appeal;

class ArchiveAppealCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var EntityRepository */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Appeal::class);
    }

    public function __invoke(ArchiveAppeal $command)
    {
        $id = $command->getId();
        $appeal = $this->repository->find($id);

        if (!$appeal) {
            throw new \InvalidArgumentException(sprintf('The appeal with ID %d was not found.', $id));
        }

        $appeal->setArchived(true);

        $this->entityManager->persist($appeal);
        $this->entityManager->flush();
    }
}
