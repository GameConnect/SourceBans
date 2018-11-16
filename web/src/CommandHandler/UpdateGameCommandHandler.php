<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\UpdateGame;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateGameCommandHandler
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

    public function __invoke(UpdateGame $command)
    {
        $game = $command->getGame();
        $icon = $game->getIcon();

        if ($icon instanceof UploadedFile) {
            $fileName = $game->getFolder().'.'.$icon->guessExtension();

            $icon->move($this->imagesDir, $fileName);
            $game->setIcon($fileName);
        } elseif ($icon instanceof File) {
            $game->setIcon($icon->getFilename());
        }

        $this->entityManager->persist($game);
        $this->entityManager->flush();
    }
}
