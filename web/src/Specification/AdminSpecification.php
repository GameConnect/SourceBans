<?php

namespace SourceBans\Specification;

use Rb\Specification\Doctrine\Query;
use Rb\Specification\Doctrine\Specification;
use SourceBans\Entity\Admin;

class AdminSpecification extends Specification
{
    public function __construct()
    {
        parent::__construct([
            new Query\Select(['serverGroups', 'webGroup']),
            new Query\LeftJoin('serverGroups', 'serverGroups'),
            new Query\LeftJoin('group', 'webGroup'),
        ]);
    }

    public function isSatisfiedBy($className): bool
    {
        return $className == Admin::class;
    }
}
