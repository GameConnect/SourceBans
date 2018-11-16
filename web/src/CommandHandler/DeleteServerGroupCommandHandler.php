<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SourceBans\Command\DeleteServerGroup;
use SourceBans\Entity\ServerGroup;

class DeleteServerGroupCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var EntityRepository */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(ServerGroup::class);
    }

    public function __invoke(DeleteServerGroup $command)
    {
        $id = $command->getId();
        $serverGroup = $this->repository->find($id);

        if (!$serverGroup) {
            throw new \InvalidArgumentException(sprintf('The server group with ID %d was not found.', $id));
        }

        $this->entityManager->remove($serverGroup);
        $this->entityManager->flush();
    }
}
