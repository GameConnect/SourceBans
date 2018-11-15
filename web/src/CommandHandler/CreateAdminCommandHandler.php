<?php

namespace SourceBans\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use SourceBans\Command\CreateAdmin;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateAdminCommandHandler
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function __invoke(CreateAdmin $command)
    {
        $admin = $command->getAdmin();

        if ($admin->getPlainPassword()) {
            $password = $this->passwordEncoder->encodePassword($admin, $admin->getPlainPassword());
            $admin->setPassword($password);
        }

        $this->entityManager->persist($admin);
        $this->entityManager->flush();
    }
}
