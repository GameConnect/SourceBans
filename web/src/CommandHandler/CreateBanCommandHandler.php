<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\CreateBan;

class CreateBanCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(CreateBan $command)
    {
        $ban = $command->getBan();

        $this->entityManager->persist($ban);
        $this->entityManager->flush();
    }
}
