<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\UnbanBan;

class UnbanBanCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(UnbanBan $command)
    {
        $ban = $command->getBan();
        $unbanAdmin = $command->getUnbanAdmin();

        $ban->setUnbanAdmin($unbanAdmin);
        $ban->setUnbanTime(new \DateTime());

        $this->entityManager->persist($ban);
        $this->entityManager->flush();
    }
}
