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
use SourceBans\CoreBundle\Form\UnbanForm;
use SourceBans\CoreBundle\Specification\Ban\IsActive;
use SourceBans\CoreBundle\Specification\Ban\IsPermanent;
use SourceBans\CoreBundle\Specification\BanSpecification;
use SourceBans\CoreBundle\Specification\ById;
use Symfony\Component\HttpFoundation\Request;
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
     * @inheritdoc
     * @return Pagerfanta
     */
    public function allBy(array $criteria, $limit = null, $page = null)
    {
        $specification = new BanSpecification;
        foreach ($criteria as $field => $value) {
            $specification->add(new Condition\Equals($field, $value));
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
    public function getBy(array $criteria)
    {
        $specification = new BanSpecification;
        foreach ($criteria as $field => $value) {
            $specification->add(new Condition\Equals($field, $value));
        }

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

        $this->objectManager->persist($entity);
        $this->objectManager->flush();
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

        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }

    /**
     * @param EntityInterface $entity
     */
    protected function preSubmit(EntityInterface $entity)
    {
        /** @var Ban $entity */
        $entity->setAdmin($this->container->get('security.token_storage')->getToken()->getUser());
        $entity->setAdminIp($this->container->get('request')->getClientIp());
    }
}
