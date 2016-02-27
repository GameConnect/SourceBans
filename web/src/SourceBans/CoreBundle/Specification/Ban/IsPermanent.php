<?php

namespace SourceBans\CoreBundle\Specification\Ban;

use Rb\Specification\Doctrine\Condition\Equals;
use SourceBans\CoreBundle\Entity\Ban;

/**
 * IsPermanent
 */
class IsPermanent extends Equals
{
    /**
     * Constructor
     * @param string|null $dqlAlias
     */
    public function __construct($dqlAlias = null)
    {
        parent::__construct('length', 0, $dqlAlias);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Ban::class;
    }
}
