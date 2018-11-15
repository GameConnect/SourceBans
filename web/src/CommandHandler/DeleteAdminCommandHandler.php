<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SourceBans\Command\DeleteAdmin;
use SourceBans\Entity\Admin;

class DeleteAdminCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var EntityRepository */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Admin::class);
    }

    public function __invoke(DeleteAdmin $command)
    {
        $id = $command->getId();
        $admin = $this->repository->find($id);

        if (!$admin) {
            throw new \InvalidArgumentException(sprintf('The admin with ID %d was not found.', $id));
        }

        $this->entityManager->remove($admin);
        $this->entityManager->flush();
    }
}
