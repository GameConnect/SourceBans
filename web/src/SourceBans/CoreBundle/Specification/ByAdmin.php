<?php

namespace SourceBans\CoreBundle\Specification;

use Rb\Specification\Doctrine\Condition\Equals;
use SourceBans\CoreBundle\Entity\Action;
use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Entity\Ban;
use SourceBans\CoreBundle\Entity\Comment;
use SourceBans\CoreBundle\Entity\Log;

/**
 * ByAdmin
 */
class ByAdmin extends Equals
{
    /**
     * Constructor
     * @param integer|Admin $admin
     * @param string|null $dqlAlias
     */
    public function __construct($admin, $dqlAlias = null)
    {
        parent::__construct('admin', $admin, $dqlAlias);
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Action::class
            || $className == Ban::class
            || $className == Comment::class
            || $className == Log::class;
    }
}
