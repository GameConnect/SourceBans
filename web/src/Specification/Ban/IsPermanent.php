<?php

namespace SourceBans\Specification\Ban;

use Rb\Specification\Doctrine\Condition;
use SourceBans\Entity\Ban;

class IsPermanent extends Condition\Equals
{
    public function __construct($dqlAlias = null)
    {
        parent::__construct('length', Ban::LENGTH_PERMANENT, $dqlAlias);
    }

    public function isSatisfiedBy($className): bool
    {
        return $className == Ban::class;
    }
}
