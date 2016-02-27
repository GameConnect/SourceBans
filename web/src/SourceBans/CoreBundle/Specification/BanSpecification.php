<?php

namespace SourceBans\CoreBundle\Specification;

use Rb\Specification\Doctrine\Query;
use Rb\Specification\Doctrine\Specification;
use SourceBans\CoreBundle\Entity\Ban;

/**
 * BanSpecification
 */
class BanSpecification extends Specification
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct([
            new Query\Select(['admin', 'server']),
            new Query\LeftJoin('admin', 'admin'),
            new Query\LeftJoin('server', 'server'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Ban::class;
    }
}
