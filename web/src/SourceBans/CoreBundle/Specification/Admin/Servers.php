<?php

namespace SourceBans\CoreBundle\Specification\Admin;

use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\CoreBundle\Entity\Admin;

/**
 * Servers
 */
class Servers extends Logic\AndX
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            new Query\Select(['serverGroups', 'servers']),
            new Query\LeftJoin('serverGroups', 'serverGroups'),
            new Query\LeftJoin('servers', 'servers', 'serverGroups')
        );
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Admin::class;
    }
}
