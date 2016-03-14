<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Condition;
use Rb\Specification\Doctrine\Logic\AndX;
use SourceBans\CoreBundle\Entity\Appeal;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\AppealAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\AppealForm;
use SourceBans\CoreBundle\Specification\AppealSpecification;
use SourceBans\CoreBundle\Specification\ById;

/**
 * AppealAdapter
 */
class AppealAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $options = [])
    {
        $specification = new AppealSpecification;

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function allBy(array $criteria, $limit = null, $page = null)
    {
        $specification = new AppealSpecification;
        foreach ($criteria as $field => $value) {
            $specification->add(new Condition\Equals($field, $value));
        }

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Appeal
     */
    public function get($id)
    {
        $specification = new AndX(
            new AppealSpecification,
            new ById($id)
        );

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Appeal
     */
    public function getBy(array $criteria)
    {
        $specification = new AppealSpecification;
        foreach ($criteria as $field => $value) {
            $specification->add(new Condition\Equals($field, $value));
        }

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Appeal
     */
    public function create(array $parameters = null)
    {
        /** @var Appeal $entity */
        $entity = new $this->entityClass;
        $entity->setUserIp($this->container->get('request')->getClientIp());

        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::APPEAL_CREATE, new AppealAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, array $parameters = null)
    {
        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::APPEAL_UPDATE, new AppealAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
        $this->dispatcher->dispatch(AdapterEvents::APPEAL_DELETE, new AppealAdapterEvent($entity));
    }

    /**
     * @param EntityInterface $entity
     * @param array $parameters
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, array $parameters = null)
    {
        $this->submitForm(AppealForm::class, $entity, $parameters);

        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }
}
