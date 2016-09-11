<?php

namespace SourceBans\CoreBundle\Entity;

use Rb\Specification\Doctrine\SpecificationRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * AdminRepository
 */
class AdminRepository extends SpecificationRepository implements UserLoaderInterface
{
    /**
     * @inheritdoc
     */
    public function loadUserByUsername($username)
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
