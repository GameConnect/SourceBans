<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Query;
use SourceBans\CoreBundle\Entity\EntityInterface;
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
    public function all($limit = null, $page = null, $sort = null, $order = null, array $criteria = [])
    {
        $specification = new BlockSpecification;
        array_map([$specification, 'add'], $criteria);
        if ($sort) {
            $specification->add(new Query\OrderBy($sort, $order));
        } else {
            $specification->add(new Query\OrderBy('createTime', Query\OrderBy::DESC));
        }

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function allBy(array $criteria, $limit = null, $page = null)
    {
        $specification = new BlockSpecification;
        array_map([$specification, 'add'], $criteria);

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function getBy(array $criteria)
    {
        throw new \RuntimeException('Not implemented');
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
