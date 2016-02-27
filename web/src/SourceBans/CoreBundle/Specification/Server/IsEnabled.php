<?php

namespace SourceBans\CoreBundle\Specification\Server;

use Rb\Specification\Doctrine\Condition\Equals;
use SourceBans\CoreBundle\Entity\Server;

/**
 * IsEnabled
 */
class IsEnabled extends Equals
{
    /**
     * Constructor
     * @param string|null $dqlAlias
     */
    public function __construct($dqlAlias = null)
    {
        parent::__construct('enabled', 1, $dqlAlias);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Server::class;
    }
}
