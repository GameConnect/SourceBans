<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SourceBans\Command\DeleteGroup;
use SourceBans\Entity\Group;

class DeleteGroupCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var EntityRepository */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Group::class);
    }

    public function __invoke(DeleteGroup $command)
    {
        $id = $command->getId();
        $group = $this->repository->find($id);

        if (!$group) {
            throw new \InvalidArgumentException(sprintf('The web group with ID %d was not found.', $id));
        }

        $this->entityManager->remove($group);
        $this->entityManager->flush();
    }
}
