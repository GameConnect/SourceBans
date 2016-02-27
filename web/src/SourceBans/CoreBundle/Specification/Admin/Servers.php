<?php

namespace SourceBans\CoreBundle\Specification\Admin;

use Rb\Specification\Doctrine\Query;
use Rb\Specification\Doctrine\Specification;
use SourceBans\CoreBundle\Entity\Admin;

/**
 * Servers
 */
class Servers extends Specification
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct([
            new Query\Select(['serverGroups', 'servers']),
            new Query\LeftJoin('serverGroups', 'serverGroups'),
            new Query\LeftJoin('servers', 'servers', 'serverGroups'),
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
