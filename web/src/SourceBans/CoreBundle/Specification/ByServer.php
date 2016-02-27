<?php

namespace SourceBans\CoreBundle\Specification;

use Rb\Specification\Doctrine\Condition\Equals;
use SourceBans\CoreBundle\Entity\Action;
use SourceBans\CoreBundle\Entity\Ban;
use SourceBans\CoreBundle\Entity\Block;
use SourceBans\CoreBundle\Entity\Report;
use SourceBans\CoreBundle\Entity\Server;

/**
 * ByServer
 */
class ByServer extends Equals
{
    /**
     * Constructor
     * @param integer|Server $server
     * @param string|null $dqlAlias
     */
    public function __construct($server, $dqlAlias = null)
    {
        parent::__construct('server', $server, $dqlAlias);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Action::class
            || $className == Ban::class
            || $className == Block::class
            || $className == Report::class;
    }
}
