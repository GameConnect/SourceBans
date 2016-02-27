<?php

namespace SourceBans\CoreBundle\Specification\Ban;

use Doctrine\ORM\QueryBuilder;
use Rb\Specification\Doctrine\AbstractSpecification;
use SourceBans\CoreBundle\Entity\Ban;

/**
 * IsActive
 */
class IsActive extends AbstractSpecification
{
    /**
     * Constructor
     * @param string|null $dqlAlias
     */
    public function __construct($dqlAlias = null)
    {
        $this->dqlAlias = $dqlAlias;
    }

    /**
     * @inheritdoc
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        if (!empty($this->dqlAlias)) {
            $dqlAlias = $this->dqlAlias;
        }

        $qb->setParameter(':now', new \DateTime);

        return $qb->expr()->andX(
            $qb->expr()->isNull($dqlAlias . '.unbanTime'),
            $qb->expr()->orX(
                $qb->expr()->eq($dqlAlias . '.length', 0),
                $qb->expr()->gt(
                    'ADDDATE(' . $dqlAlias . '.createTime, INTERVAL ' . $dqlAlias . '.length MINUTE)',
                    ':now'
                )
            )
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
