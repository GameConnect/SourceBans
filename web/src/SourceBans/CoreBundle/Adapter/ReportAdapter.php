<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\Report;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\ReportAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\ReportForm;
use SourceBans\CoreBundle\Specification\ById;
use SourceBans\CoreBundle\Specification\ReportSpecification;
use Symfony\Component\HttpFoundation\Request;

/**
 * ReportAdapter
 */
class ReportAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $criteria = [])
    {
        $specification = new Logic\AndX(
            new ReportSpecification,
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
        $specification = new ReportSpecification;
        array_map([$specification, 'add'], $criteria);

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Report
     */
    public function get($id)
    {
        $specification = new Logic\AndX(
            new ReportSpecification,
            new ById($id)
        );

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Report
     */
    public function getBy(array $criteria)
    {
        $specification = new ReportSpecification;
        array_map([$specification, 'add'], $criteria);

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Report
     */
    public function create(Request $request)
    {
        $entity = new $this->entityClass;

        $this->preSubmit($entity);
        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::REPORT_CREATE, new ReportAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, Request $request)
    {
        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::REPORT_UPDATE, new ReportAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        parent::delete($entity);

        $this->dispatcher->dispatch(AdapterEvents::REPORT_DELETE, new ReportAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function persist(EntityInterface $entity)
    {
        $this->preSubmit($entity);

        parent::persist($entity);
    }

    /**
     * @param EntityInterface $entity
     */
    public function archive(EntityInterface $entity)
    {
        /** @var Report $entity */
        $entity->setArchived(true);

        parent::persist($entity);

        $this->dispatcher->dispatch(AdapterEvents::REPORT_ARCHIVE, new ReportAdapterEvent($entity));
    }

    /**
     * @param EntityInterface $entity
     * @param Request $request
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, Request $request)
    {
        $this->submitForm(ReportForm::class, $entity, $request);

        parent::persist($entity);
    }

    /**
     * @param EntityInterface $entity
     */
    protected function preSubmit(EntityInterface $entity)
    {
        /** @var Report $entity */
        $entity->setUserIp($this->container->get('request_stack')->getCurrentRequest()->getClientIp());
    }
}
