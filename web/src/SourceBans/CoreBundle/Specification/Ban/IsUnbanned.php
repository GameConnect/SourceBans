<?php

namespace SourceBans\CoreBundle\Specification\Ban;

use Rb\Specification\Doctrine\Condition\IsNotNull;
use SourceBans\CoreBundle\Entity\Ban;

/**
 * IsUnbanned
 */
class IsUnbanned extends IsNotNull
{
    /**
     * Constructor
     * @param string|null $dqlAlias
     */
    public function __construct($dqlAlias = null)
    {
        parent::__construct('unbanTime', $dqlAlias);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Ban::class;
    }
}
