<?php

namespace SourceBans\CoreBundle\Specification\Ban;

use Rb\Specification\Doctrine\Logic;
use SourceBans\CoreBundle\Entity\Ban;

/**
 * IsActive
 */
class IsActive extends Logic\AndX
{
    /**
     * Constructor
     * @param string|null $dqlAlias
     */
    public function __construct($dqlAlias = null)
    {
        parent::__construct(
            new Logic\Not(new IsExpired($dqlAlias)),
            new Logic\Not(new IsUnbanned($dqlAlias))
        );
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Ban::class;
    }
}
