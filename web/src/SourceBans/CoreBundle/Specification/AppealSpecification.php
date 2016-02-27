<?php

namespace SourceBans\CoreBundle\Specification;

use Rb\Specification\Doctrine\Query;
use Rb\Specification\Doctrine\Specification;
use SourceBans\CoreBundle\Entity\Appeal;

/**
 * AppealSpecification
 */
class AppealSpecification extends Specification
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct([
            new Query\Select('ban'),
            new Query\Join('ban', 'ban'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Appeal::class;
    }
}
