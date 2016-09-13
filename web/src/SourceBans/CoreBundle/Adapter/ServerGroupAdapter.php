<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\ServerGroup;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\ServerGroupAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\ServerGroupForm;
use Symfony\Component\HttpFoundation\Request;

/**
 * ServerGroupAdapter
 */
class ServerGroupAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $criteria = [])
    {
        $query = $this->repository->createQueryBuilder('serverGroup')
            ->orderBy(sprintf('serverGroup.%s', $sort ?: 'name'), $order)
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
     * @return ServerGroup
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @inheritdoc
     * @return ServerGroup
     */
    public function getBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * @inheritdoc
     * @return ServerGroup
     */
    public function create(Request $request)
    {
        $entity = new $this->entityClass;

        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::SERVER_GROUP_CREATE, new ServerGroupAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, Request $request)
    {
        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::SERVER_GROUP_UPDATE, new ServerGroupAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        parent::delete($entity);

        $this->dispatcher->dispatch(AdapterEvents::SERVER_GROUP_DELETE, new ServerGroupAdapterEvent($entity));
    }

    /**
     * @param EntityInterface $entity
     * @param Request $request
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, Request $request)
    {
        $this->submitForm(ServerGroupForm::class, $entity, $request);

        parent::persist($entity);
    }
}
