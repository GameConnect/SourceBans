<?php

namespace SourceBans\Specification\Server;

use Rb\Specification\Doctrine\Condition;
use SourceBans\Entity\Server;

class IsEnabled extends Condition\Equals
{
    public function __construct($dqlAlias = null)
    {
        parent::__construct('enabled', 1, $dqlAlias);
    }

    public function isSatisfiedBy($className): bool
    {
        return $className == Server::class;
    }
}
