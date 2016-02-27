<?php

namespace SourceBans\CoreBundle\Specification;

use Rb\Specification\Doctrine\Query;
use Rb\Specification\Doctrine\Specification;
use SourceBans\CoreBundle\Entity\Admin;

/**
 * AdminSpecification
 */
class AdminSpecification extends Specification
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct([
            new Query\Select(['serverGroups', 'webGroup']),
            new Query\LeftJoin('serverGroups', 'serverGroups'),
            new Query\LeftJoin('group', 'webGroup'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Admin::class;
    }
}
