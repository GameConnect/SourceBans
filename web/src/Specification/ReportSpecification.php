<?php

namespace SourceBans\Specification;

use Rb\Specification\Doctrine\Query;
use Rb\Specification\Doctrine\Specification;
use SourceBans\Entity\Report;

class ReportSpecification extends Specification
{
    public function __construct()
    {
        parent::__construct([
            new Query\Select('server'),
            new Query\LeftJoin('server', 'server'),
        ]);
    }

    public function isSatisfiedBy($className): bool
    {
        return $className == Report::class;
    }
}
