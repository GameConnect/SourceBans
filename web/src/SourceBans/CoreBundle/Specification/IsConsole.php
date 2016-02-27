<?php

namespace SourceBans\CoreBundle\Specification;

use Rb\Specification\Doctrine\Condition\IsNull;
use SourceBans\CoreBundle\Entity\Action;
use SourceBans\CoreBundle\Entity\Ban;

/**
 * IsConsole
 */
class IsConsole extends IsNull
{
    /**
     * Constructor
     * @param string|null $dqlAlias
     */
    public function __construct($dqlAlias = null)
    {
        parent::__construct('admin', $dqlAlias);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Action::class
            || $className == Ban::class;
    }
}
