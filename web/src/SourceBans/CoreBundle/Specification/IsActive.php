<?php

namespace SourceBans\CoreBundle\Specification;

use Rb\Specification\Doctrine\Condition\Equals;
use SourceBans\CoreBundle\Entity\Appeal;
use SourceBans\CoreBundle\Entity\Report;

/**
 * IsActive
 */
class IsActive extends Equals
{
    /**
     * Constructor
     * @param string|null $dqlAlias
     */
    public function __construct($dqlAlias = null)
    {
        parent::__construct('archived', 0, $dqlAlias);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Appeal::class
            || $className == Report::class;
    }
}
