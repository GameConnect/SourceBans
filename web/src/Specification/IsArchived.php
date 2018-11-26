<?php

namespace SourceBans\Specification;

use Rb\Specification\Doctrine\Condition;
use SourceBans\Entity\Appeal;
use SourceBans\Entity\Report;

class IsArchived extends Condition\Equals
{
    public function __construct($dqlAlias = null)
    {
        parent::__construct('archived', 1, $dqlAlias);
    }

    public function isSatisfiedBy($className)
    {
        return $className == Appeal::class
            || $className == Report::class;
    }
}
