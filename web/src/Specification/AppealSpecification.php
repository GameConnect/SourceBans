<?php

namespace SourceBans\Specification;

use Rb\Specification\Doctrine\Query;
use Rb\Specification\Doctrine\Specification;
use SourceBans\Entity\Appeal;

class AppealSpecification extends Specification
{
    public function __construct()
    {
        parent::__construct([
            new Query\Select('ban'),
            new Query\Join('ban', 'ban'),
        ]);
    }

    public function isSatisfiedBy($className): bool
    {
        return $className == Appeal::class;
    }
}
