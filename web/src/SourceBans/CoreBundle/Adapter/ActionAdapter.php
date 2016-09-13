<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\CoreBundle\Entity\Action;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Specification\ActionSpecification;
use SourceBans\CoreBundle\Specification\ById;
use Symfony\Component\HttpFoundation\Request;

/**
 * ActionAdapter
 */
class ActionAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $criteria = [])
    {
        $specification = new Logic\AndX(
            new ActionSpecification,
            new Query\OrderBy($sort ?: 'createTime', $order)
        );
        array_map([$specification, 'add'], $criteria);

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function allBy(array $criteria, $limit = null, $page = null)
    {
        $specification = new ActionSpecification;
        array_map([$specification, 'add'], $criteria);

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Action
     */
    public function get($id)
    {
        $specification = new Logic\AndX(
            new ActionSpecification,
            new ById($id)
        );

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Action
     */
    public function getBy(array $criteria)
    {
        $specification = new ActionSpecification;
        array_map([$specification, 'add'], $criteria);

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
