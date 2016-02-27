<?php

namespace SourceBans\CoreBundle\Specification;

use Doctrine\ORM\QueryBuilder;
use Rb\Specification\Doctrine\Specification;
use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Entity\Ban;

/**
 * CommunityId
 */
class CommunityId extends Specification
{
    /**
     * @inheritdoc
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $qb->addSelect('
            (CASE
                WHEN ' . $dqlAlias . '.steam LIKE "STEAM_%" THEN 76561197960265728 + CAST(SUBSTRING(' . $dqlAlias . '.steam, 9, 1) AS UNSIGNED) + CAST(SUBSTRING(' . $dqlAlias . '.steam, 11) * 2 AS UNSIGNED)
                WHEN ' . $dqlAlias . '.steam LIKE "[U:%]" THEN 76561197960265728 + CAST(SUBSTRING(' . $dqlAlias . '.steam, 6, CHAR_LENGTH(' . $dqlAlias . '.steam) - 6) AS UNSIGNED)
            END) AS community_id
        ');
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Admin::class
            || $className == Ban::class;
    }
}
