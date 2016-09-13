<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\AdminAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\AdminForm;
use SourceBans\CoreBundle\Specification\AdminSpecification;
use SourceBans\CoreBundle\Specification\ById;
use Symfony\Component\HttpFoundation\Request;

/**
 * AdminAdapter
 */
class AdminAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $criteria = [])
    {
        $specification = new Logic\AndX(
            new AdminSpecification,
            new Query\OrderBy($sort ?: 'name', $order)
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
        $specification = new AdminSpecification;
        array_map([$specification, 'add'], $criteria);

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Admin
     */
    public function get($id)
    {
        $specification = new Logic\AndX(
            new AdminSpecification,
            new ById($id)
        );

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Admin
     */
    public function getBy(array $criteria)
    {
        $specification = new AdminSpecification;
        array_map([$specification, 'add'], $criteria);

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Admin
     */
    public function create(Request $request)
    {
        $entity = new $this->entityClass;

        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::ADMIN_CREATE, new AdminAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, Request $request)
    {
        $this->processForm($entity, $request);
        $this->dispatcher->dispatch(AdapterEvents::ADMIN_UPDATE, new AdminAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        parent::delete($entity);

        $this->dispatcher->dispatch(AdapterEvents::ADMIN_DELETE, new AdminAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function persist(EntityInterface $entity)
    {
        $this->postSubmit($entity);

        parent::persist($entity);
    }

    /**
     * @param EntityInterface $entity
     * @param Request $request
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, Request $request)
    {
        $this->submitForm(AdminForm::class, $entity, $request);
        $this->postSubmit($entity);

        parent::persist($entity);
    }

    /**
     * @param EntityInterface $entity
     */
    protected function postSubmit(EntityInterface $entity)
    {
        /** @var Admin $entity */
        if ($entity->getPlainPassword() == '') {
            $entity->setPassword(null);
        } elseif ($entity->getPlainPassword() != AdminForm::PASSWORD_PLACEHOLDER_VALUE) {
            $encoder = $this->container->get('security.password_encoder');
            $password = $encoder->encodePassword($entity, $entity->getPlainPassword());

            $entity->setPassword($password);
        }
    }
}
