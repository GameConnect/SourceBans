<?php

namespace SourceBans\Repository;

use Rb\Specification\Doctrine\SpecificationRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminRepository extends SpecificationRepository implements UserLoaderInterface
{
    public function loadUserByUsername($username): ?UserInterface
    {
        return $this->createQueryBuilder('admin')
            ->addSelect(['serverGroups', 'webGroup'])
            ->leftJoin('admin.serverGroups', 'serverGroups')
            ->leftJoin('admin.group', 'webGroup')
            ->where('admin.name = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
