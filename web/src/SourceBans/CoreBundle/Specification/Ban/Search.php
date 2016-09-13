<?php

namespace SourceBans\CoreBundle\Specification\Ban;

use Rb\Specification\Doctrine\Condition;
use Rb\Specification\Doctrine\Logic;
use SourceBans\CoreBundle\Entity\Ban;

/**
 * Search
 */
class Search extends Logic\OrX
{
    /**
     * Constructor
     * @param string $query
     * @param string|null $dqlAlias
     */
    public function __construct($query, $dqlAlias = null)
    {
        parent::__construct(
            new Condition\Like('steam', $query, Condition\Like::CONTAINS, $dqlAlias),
            new Condition\Like('ip', $query, Condition\Like::CONTAINS, $dqlAlias),
            new Condition\Like('name', $query, Condition\Like::CONTAINS, $dqlAlias)
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
