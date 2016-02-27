<?php

namespace SourceBans\CoreBundle\Specification;

use Rb\Specification\Doctrine\Query;
use Rb\Specification\Doctrine\Specification;
use SourceBans\CoreBundle\Entity\Log;

/**
 * LogSpecification
 */
class LogSpecification extends Specification
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct([
            new Query\Select('admin'),
            new Query\LeftJoin('admin', 'admin'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Log::class;
    }
}
