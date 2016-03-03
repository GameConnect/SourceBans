<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Condition;
use Rb\Specification\Doctrine\Logic\AndX;
use Rb\Specification\Doctrine\Query;
use SourceBans\CoreBundle\Entity\Admin;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\Server;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\AdminAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\AdminForm;
use SourceBans\CoreBundle\Specification\Admin\Servers;
use SourceBans\CoreBundle\Specification\AdminSpecification;
use SourceBans\CoreBundle\Specification\ById;

/**
 * AdminAdapter
 */
class AdminAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $options = [])
    {
        $specification = new AndX(
            new AdminSpecification,
            new Query\OrderBy($sort ?: 'name', $order)
        );

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @param integer|Server $server
     * @param integer $limit
     * @param integer $page
     * @return Pagerfanta
     */
    public function allByServer($server, $limit = null, $page = null)
    {
        $specification = new AndX(
            new Servers,
            new Condition\Equals('id', $server, 'servers')
        );

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Admin
     */
    public function get($id)
    {
        $specification = new AndX(
            new AdminSpecification,
            new ById($id)
        );

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Admin
     */
    public function create(array $parameters = null)
    {
        $entity = new $this->entityClass;

        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::ADMIN_CREATE, new AdminAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, array $parameters = null)
    {
        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::ADMIN_UPDATE, new AdminAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
        $this->dispatcher->dispatch(AdapterEvents::ADMIN_DELETE, new AdminAdapterEvent($entity));
    }

    /**
     * @param EntityInterface $entity
     * @param array $parameters
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, array $parameters = null)
    {
        /** @var Admin $entity */
        $form = $this->submitForm(AdminForm::class, $entity, $parameters);

        if ($form->has('plainPassword') && $form->get('plainPassword')->getData() != '') {
            $encoder = $this->container->get('security.password_encoder');
            $password = $encoder->encodePassword($entity, $form->get('plainPassword')->getData());

            $entity->setPassword($password);
        }

        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }
}
