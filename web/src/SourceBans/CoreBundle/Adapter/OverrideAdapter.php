<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\Override;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\OverrideAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\OverrideForm;
use Symfony\Component\HttpFoundation\Request;

/**
 * OverrideAdapter
 */
class OverrideAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $criteria = [])
    {
        $query = $this->repository->createQueryBuilder('override')
            ->orderBy(sprintf('override.%s', $sort ?: 'type'), $order)
            ->addOrderBy('override.name')
            ->getQuery();

        return static::queryToPager($query, $limit, $page, false);
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
     * @return Override
     */
    public function create(Request $request)
    {
        $entity = new $this->entityClass;

        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::OVERRIDE_CREATE, new OverrideAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, Request $request)
    {
        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::OVERRIDE_UPDATE, new OverrideAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        parent::delete($entity);

        $this->dispatcher->dispatch(AdapterEvents::OVERRIDE_DELETE, new OverrideAdapterEvent($entity));
    }

    /**
     * @param EntityInterface $entity
     * @param Request $request
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, Request $request)
    {
        $this->submitForm(OverrideForm::class, $entity, $request);

        parent::persist($entity);
    }
}
