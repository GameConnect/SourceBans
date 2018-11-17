<?php

namespace SourceBans\Specification;

use Rb\Specification\Doctrine\Query;
use Rb\Specification\Doctrine\Specification;
use SourceBans\Entity\Ban;

class BanSpecification extends Specification
{
    public function __construct()
    {
        parent::__construct([
            new Query\Select(['admin', 'server']),
            new Query\LeftJoin('admin', 'admin'),
            new Query\LeftJoin('server', 'server'),
        ]);
    }

    public function isSatisfiedBy($className): bool
    {
        return $className == Ban::class;
    }
}
