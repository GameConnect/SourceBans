<?php

namespace SourceBans\Specification;

use Rb\Specification\Doctrine\Query;
use Rb\Specification\Doctrine\Specification;
use SourceBans\Entity\Server;

class ServerSpecification extends Specification
{
    public function __construct()
    {
        parent::__construct([
            new Query\Select('game'),
            new Query\Join('game', 'game'),
        ]);
    }

    public function isSatisfiedBy($className): bool
    {
        return $className == Server::class;
    }
}
