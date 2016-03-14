<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Condition;
use Rb\Specification\Doctrine\Logic\AndX;
use Rb\Specification\Doctrine\Query;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\Server;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\ServerAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\ServerForm;
use SourceBans\CoreBundle\Specification\ById;
use SourceBans\CoreBundle\Specification\ServerSpecification;

/**
 * ServerAdapter
 */
class ServerAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $options = [])
    {
        $specification = new ServerSpecification;
        if ($sort) {
            $specification->add(new Query\OrderBy($sort, $order));
        } else {
            $specification->add(new Query\OrderBy('name', null, 'game'));
            $specification->add(new Query\OrderBy('host'));
            $specification->add(new Query\OrderBy('port'));
        }

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function allBy(array $criteria, $limit = null, $page = null)
    {
        $specification = new ServerSpecification;
        foreach ($criteria as $field => $value) {
            $specification->add(new Condition\Equals($field, $value));
        }

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Server
     */
    public function get($id)
    {
        $specification = new AndX(
            new ServerSpecification,
            new ById($id)
        );

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Server
     */
    public function getBy(array $criteria)
    {
        $specification = new ServerSpecification;
        foreach ($criteria as $field => $value) {
            $specification->add(new Condition\Equals($field, $value));
        }

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Server
     */
    public function create(array $parameters = null)
    {
        $entity = new $this->entityClass;

        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::SERVER_CREATE, new ServerAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, array $parameters = null)
    {
        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::SERVER_UPDATE, new ServerAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
        $this->dispatcher->dispatch(AdapterEvents::SERVER_DELETE, new ServerAdapterEvent($entity));
    }

    /**
     * @param EntityInterface $entity
     * @param array $parameters
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, array $parameters = null)
    {
        $this->submitForm(ServerForm::class, $entity, $parameters);

        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }
}
