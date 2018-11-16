<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SourceBans\Command\DeleteServer;
use SourceBans\Entity\Server;

class DeleteServerCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var EntityRepository */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Server::class);
    }

    public function __invoke(DeleteServer $command)
    {
        $id = $command->getId();
        $server = $this->repository->find($id);

        if (!$server) {
            throw new \InvalidArgumentException(sprintf('The server with ID %d was not found.', $id));
        }

        $this->entityManager->remove($server);
        $this->entityManager->flush();
    }
}
