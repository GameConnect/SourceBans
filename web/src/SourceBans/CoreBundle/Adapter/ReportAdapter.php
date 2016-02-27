<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Logic\AndX;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\Report;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\ReportAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\ReportForm;
use SourceBans\CoreBundle\Specification\ById;
use SourceBans\CoreBundle\Specification\ReportSpecification;

/**
 * ReportAdapter
 */
class ReportAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = 25, $page = 1, $sort = null, $order = null, array $options = [])
    {
        $specification = new ReportSpecification;
        $pager = static::queryToPager($this->repository->match($specification));

        return $pager->setCurrentPage($page)->setMaxPerPage($limit);
    }

    /**
     * @inheritdoc
     * @return Report
     */
    public function get($id)
    {
        $specification = new AndX(
            new ReportSpecification,
            new ById($id)
        );

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Report
     */
    public function create(array $parameters)
    {
        /** @var Report $entity */
        $entity = new $this->entityClass;
        $entity->setUserIp($this->container->get('request')->getClientIp());

        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::REPORT_CREATE, new ReportAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, array $parameters)
    {
        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::REPORT_UPDATE, new ReportAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
        $this->dispatcher->dispatch(AdapterEvents::REPORT_DELETE, new ReportAdapterEvent($entity));
    }

    /**
     * @param EntityInterface $entity
     * @param array $parameters
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, array $parameters)
    {
        $this->submitForm(ReportForm::class, $entity, $parameters);

        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }
}
