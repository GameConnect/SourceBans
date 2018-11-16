<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SourceBans\Command\DeleteGame;
use SourceBans\Entity\Game;

class DeleteGameCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var EntityRepository */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Game::class);
    }

    public function __invoke(DeleteGame $command)
    {
        $id = $command->getId();
        $game = $this->repository->find($id);

        if (!$game) {
            throw new \InvalidArgumentException(sprintf('The game with ID %d was not found.', $id));
        }

        $this->entityManager->remove($game);
        $this->entityManager->flush();
    }
}
