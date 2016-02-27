<?php

namespace SourceBans\CoreBundle\Specification;

use Rb\Specification\Doctrine\Condition\In;
use SourceBans\CoreBundle\Entity\Game;
use SourceBans\CoreBundle\Entity\Server;
use SourceBans\CoreBundle\Entity\ServerGroup;

/**
 * ByServers
 */
class ByServers extends In
{
    /**
     * Constructor
     * @param integer|integer[]|Server|Server[] $servers
     * @param string|null $dqlAlias
     */
    public function __construct($servers, $dqlAlias = null)
    {
        parent::__construct('servers', $servers, $dqlAlias);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Game::class
            || $className == ServerGroup::class;
    }
}
