<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\DeleteGame;

class DeleteGameCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(DeleteGame $command)
    {
        $game = $command->getGame();

        $this->entityManager->remove($game);
        $this->entityManager->flush();
    }
}
