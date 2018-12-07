<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\DeleteBan;

class DeleteBanCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(DeleteBan $command)
    {
        $ban = $command->getBan();

        $this->entityManager->remove($ban);
        $this->entityManager->flush();
    }
}
