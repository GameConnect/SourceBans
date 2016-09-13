<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\CoreBundle\Entity\Appeal;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\AppealAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\AppealForm;
use SourceBans\CoreBundle\Specification\AppealSpecification;
use SourceBans\CoreBundle\Specification\ById;
use Symfony\Component\HttpFoundation\Request;

/**
 * AppealAdapter
 */
class AppealAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $criteria = [])
    {
        $specification = new Logic\AndX(
            new AppealSpecification,
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
        $specification = new AppealSpecification;
        array_map([$specification, 'add'], $criteria);

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Appeal
     */
    public function get($id)
    {
        $specification = new Logic\AndX(
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
        array_map([$specification, 'add'], $criteria);

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Appeal
     */
    public function create(Request $request)
    {
        $entity = new $this->entityClass;

        $this->preSubmit($entity);
        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::APPEAL_CREATE, new AppealAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, Request $request)
    {
        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::APPEAL_UPDATE, new AppealAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        parent::delete($entity);

        $this->dispatcher->dispatch(AdapterEvents::APPEAL_DELETE, new AppealAdapterEvent($entity));
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
        /** @var Appeal $entity */
        $entity->setArchived(true);

        parent::persist($entity);

        $this->dispatcher->dispatch(AdapterEvents::APPEAL_ARCHIVE, new AppealAdapterEvent($entity));
    }

    /**
     * @param EntityInterface $entity
     * @param Request $request
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, Request $request)
    {
        $this->submitForm(AppealForm::class, $entity, $request);

        parent::persist($entity);
    }

    /**
     * @param EntityInterface $entity
     */
    protected function preSubmit(EntityInterface $entity)
    {
        /** @var Appeal $entity */
        $entity->setUserIp($this->container->get('request_stack')->getCurrentRequest()->getClientIp());
    }
}
