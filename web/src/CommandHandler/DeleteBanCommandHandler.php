<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SourceBans\Command\DeleteBan;
use SourceBans\Entity\Ban;

class DeleteBanCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var EntityRepository */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Ban::class);
    }

    public function __invoke(DeleteBan $command)
    {
        $id = $command->getId();
        $ban = $this->repository->find($id);

        if (!$ban) {
            throw new \InvalidArgumentException(sprintf('The ban with ID %d was not found.', $id));
        }

        $this->entityManager->remove($ban);
        $this->entityManager->flush();
    }
}
