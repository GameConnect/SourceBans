<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\Group;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\GroupAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\GroupForm;
use Symfony\Component\HttpFoundation\Request;

/**
 * GroupAdapter
 */
class GroupAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $criteria = [])
    {
        $query = $this->repository->createQueryBuilder('webGroup')
            ->orderBy(sprintf('webGroup.%s', $sort ?: 'name'), $order)
            ->getQuery();

        return static::queryToPager($query, $limit, $page);
    }

    /**
     * @inheritdoc
     */
    public function allBy(array $criteria, $limit = null, $page = null)
    {
        $offset = (null === $page ? null : ($page - 1) * $limit);

        return $this->repository->findBy($criteria, null, $limit, $offset);
    }

    /**
     * @inheritdoc
     * @return Group
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @inheritdoc
     * @return Group
     */
    public function getBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * @inheritdoc
     * @return Group
     */
    public function create(Request $request)
    {
        $entity = new $this->entityClass;

        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::GROUP_CREATE, new GroupAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, Request $request)
    {
        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::GROUP_UPDATE, new GroupAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        parent::delete($entity);

        $this->dispatcher->dispatch(AdapterEvents::GROUP_DELETE, new GroupAdapterEvent($entity));
    }

    /**
     * @param EntityInterface $entity
     * @param Request $request
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, Request $request)
    {
        $this->submitForm(GroupForm::class, $entity, $request);

        parent::persist($entity);
    }
}
