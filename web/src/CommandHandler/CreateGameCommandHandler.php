<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\CreateGame;

class CreateGameCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $imagesDir;

    public function __construct(EntityManagerInterface $entityManager, string $imagesDir)
    {
        $this->entityManager = $entityManager;
        $this->imagesDir = $imagesDir;
    }

    public function __invoke(CreateGame $command)
    {
        $game = $command->getGame();
        $icon = $game->getIcon();
        $fileName = $game->getFolder().'.'.$icon->guessExtension();

        $icon->move($this->imagesDir, $fileName);
        $game->setIcon($fileName);

        $this->entityManager->persist($game);
        $this->entityManager->flush();
    }
}
