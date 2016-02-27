<?php

namespace SourceBans\CoreBundle\Specification;

use Rb\Specification\Doctrine\Condition\In;
use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Entity\Group;
use SourceBans\CoreBundle\Entity\ServerGroup;

/**
 * ByAdmins
 */
class ByAdmins extends In
{
    /**
     * Constructor
     * @param integer|integer[]|Admin|Admin[] $admins
     * @param string|null $dqlAlias
     */
    public function __construct($admins, $dqlAlias = null)
    {
        parent::__construct('admins', $admins, $dqlAlias);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Group::class
            || $className == ServerGroup::class;
    }
}
