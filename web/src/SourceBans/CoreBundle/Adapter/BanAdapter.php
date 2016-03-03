<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Condition;
use Rb\Specification\Doctrine\Logic\AndX;
use Rb\Specification\Doctrine\Query;
use SourceBans\CoreBundle\Entity\Ban;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Event\AdapterEvents;
use SourceBans\CoreBundle\Event\BanAdapterEvent;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\BanForm;
use SourceBans\CoreBundle\Specification\Ban\IsActive;
use SourceBans\CoreBundle\Specification\Ban\IsPermanent;
use SourceBans\CoreBundle\Specification\BanSpecification;
use SourceBans\CoreBundle\Specification\ById;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * BanAdapter
 */
class BanAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $options = [])
    {
        $resolver = new OptionsResolver;
        $resolver->setDefault('active', false);
        $options = $resolver->resolve($options);

        $specification = new BanSpecification;
        if ($sort) {
            $specification->add(new Query\OrderBy($sort, $order));
        } else {
            $specification->add(new Query\OrderBy('createTime', Query\OrderBy::DESC));
        }
        if ($options['active']) {
            $specification->add(new IsActive);
        }

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @param integer $type
     * @param integer $limit
     * @param integer $page
     * @param array $options
     * @return Pagerfanta
     */
    public function allByType($type, $limit = null, $page = null, array $options = [])
    {
        $resolver = new OptionsResolver;
        $resolver->setDefault('permanent', false);
        $options = $resolver->resolve($options);

        $specification = new AndX(
            new BanSpecification,
            new Condition\Equals('type', $type)
        );
        if ($options['permanent']) {
            $specification->add(new IsPermanent);
        }

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Ban
     */
    public function get($id)
    {
        $specification = new AndX(
            new BanSpecification,
            new ById($id)
        );

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Ban
     */
    public function create(array $parameters = null)
    {
        /** @var Ban $entity */
        $entity = new $this->entityClass;
        $entity->setAdmin($this->container->get('security.token_storage')->getToken()->getUser());
        $entity->setAdminIp($this->container->get('request')->getClientIp());

        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::BAN_CREATE, new BanAdapterEvent($entity));

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, array $parameters = null)
    {
        $this->processForm($entity, $parameters);
        $this->dispatcher->dispatch(AdapterEvents::BAN_UPDATE, new BanAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
        $this->dispatcher->dispatch(AdapterEvents::BAN_DELETE, new BanAdapterEvent($entity));
    }

    /**
     * @inheritdoc
     * @param string $reason
     */
    public function unban(EntityInterface $entity, $reason = null)
    {
        /** @var Ban $entity */
        $entity->setUnbanAdmin($this->container->get('security.token_storage')->getToken()->getUser());
        $entity->setUnbanReason($reason);
        $entity->setUnbanTime(new \DateTime);

        $this->processForm($entity);
        $this->dispatcher->dispatch(AdapterEvents::BAN_UNBAN, new BanAdapterEvent($entity));
    }

    /**
     * @param EntityInterface $entity
     * @param array $parameters
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, array $parameters = null)
    {
        $this->submitForm(BanForm::class, $entity, $parameters);

        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }
}
