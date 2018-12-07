<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\DeleteAdmin;

class DeleteAdminCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(DeleteAdmin $command)
    {
        $admin = $command->getAdmin();

        $this->entityManager->remove($admin);
        $this->entityManager->flush();
    }
}
