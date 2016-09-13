<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\CoreBundle\Entity\Ban;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\BanAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\BanForm;
use SourceBans\CoreBundle\Form\UnbanForm;
use SourceBans\CoreBundle\Specification\BanSpecification;
use SourceBans\CoreBundle\Specification\ById;
use Symfony\Component\HttpFoundation\Request;

/**
 * BanAdapter
 */
class BanAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $criteria = [])
    {
        $specification = new BanSpecification;
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
        $specification = new BanSpecification;
        array_map([$specification, 'add'], $criteria);

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Ban
     */
    public function get($id)
    {
        $specification = new Logic\AndX(
            new BanSpecification,
            new ById($id)
        );

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Ban
     */
    public function getBy(array $criteria)
    {
        $specification = new BanSpecification;
        array_map([$specification, 'add'], $criteria);

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Ban
     */
    public function create(Request $request)
    {
        $entity = new $this->entityClass;

        $this->preSubmit($entity);
        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::BAN_CREATE, new BanAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, Request $request)
    {
        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::BAN_UPDATE, new BanAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        parent::delete($entity);

        $this->dispatcher->dispatch(AdapterEvents::BAN_DELETE, new BanAdapterEvent($entity));
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
     * @param Request $request
     */
    public function unban(EntityInterface $entity, Request $request)
    {
        /** @var Ban $entity */
        $this->submitForm(UnbanForm::class, $entity, $request);

        $entity->setUnbanAdmin($this->container->get('security.token_storage')->getToken()->getUser());
        $entity->setUnbanTime(new \DateTime);

        parent::persist($entity);

        $this->dispatcher->dispatch(AdapterEvents::BAN_UNBAN, new BanAdapterEvent($entity));
    }

    /**
     * @param EntityInterface $entity
     * @param Request $request
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, Request $request)
    {
        $this->submitForm(BanForm::class, $entity, $request);

        parent::persist($entity);
    }

    /**
     * @param EntityInterface $entity
     */
    protected function preSubmit(EntityInterface $entity)
    {
        /** @var Ban $entity */
        $entity->setAdmin($this->container->get('security.token_storage')->getToken()->getUser());
        $entity->setAdminIp($this->container->get('request_stack')->getCurrentRequest()->getClientIp());
    }
}
