<?php

namespace SourceBans\CoreBundle\Specification\Ban;

use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Specification;
use SourceBans\CoreBundle\Entity\Ban;

/**
 * IsInactive
 */
class IsInactive extends Specification
{
    /**
     * Constructor
     * @param string|null $dqlAlias
     */
    public function __construct($dqlAlias = null)
    {
        parent::__construct([
            new Logic\OrX(
                new IsExpired($dqlAlias),
                new IsUnbanned($dqlAlias)
            ),
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
