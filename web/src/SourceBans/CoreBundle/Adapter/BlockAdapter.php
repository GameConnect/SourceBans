<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Condition;
use Rb\Specification\Doctrine\Logic\AndX;
use Rb\Specification\Doctrine\Query;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\Block;
use SourceBans\CoreBundle\Specification\ById;
use SourceBans\CoreBundle\Specification\BlockSpecification;
use Symfony\Component\HttpFoundation\Request;

/**
 * BlockAdapter
 */
class BlockAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $options = [])
    {
        $specification = new AndX(
            new BlockSpecification,
            new Query\OrderBy('createTime', Query\OrderBy::DESC)
        );

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function allBy(array $criteria, $limit = null, $page = null)
    {
        $specification = new AndX(
            new BlockSpecification,
            new Query\OrderBy('createTime', Query\OrderBy::DESC)
        );
        foreach ($criteria as $field => $value) {
            $specification->add(new Condition\Equals($field, $value));
        }

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Block
     */
    public function get($id)
    {
        $specification = new AndX(
            new BlockSpecification,
            new ById($id)
        );

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Block
     */
    public function getBy(array $criteria)
    {
        $specification = new BlockSpecification;
        foreach ($criteria as $field => $value) {
            $specification->add(new Condition\Equals($field, $value));
        }

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function create(Request $request)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, Request $request)
    {
        throw new \RuntimeException('Not implemented');
    }
}
