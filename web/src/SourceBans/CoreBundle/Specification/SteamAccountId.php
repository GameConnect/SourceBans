<?php

namespace SourceBans\CoreBundle\Specification;

use Doctrine\ORM\QueryBuilder;
use Rb\Specification\Doctrine\Specification;
use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Entity\Ban;
use SourceBans\CoreBundle\Entity\Report;

/**
 * SteamAccountId
 */
class SteamAccountId extends Specification
{
    /**
     * @inheritdoc
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $qb->addSelect('
            (CASE
                WHEN ' . $dqlAlias . '.steam LIKE "STEAM_%" THEN CAST(SUBSTRING(' . $dqlAlias . '.steam, 9, 1) AS UNSIGNED) + CAST(SUBSTRING(' . $dqlAlias . '.steam, 11) * 2 AS UNSIGNED)
                WHEN ' . $dqlAlias . '.steam LIKE "[U:%]" THEN CAST(SUBSTRING(' . $dqlAlias . '.steam, 6, CHAR_LENGTH(' . $dqlAlias . '.steam) - 6) AS UNSIGNED)
            END) AS accountId
        ');
    }

    /**
     * @inheritdoc
     */
    public function isSatisfiedBy($className)
    {
        return $className == Admin::class
            || $className == Ban::class
            || $className == Report::class;
    }
}
