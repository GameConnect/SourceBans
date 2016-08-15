<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\Demo;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\DemoForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * DemoAdapter
 */
class DemoAdapter extends AbstractAdapter
{
    /**
     * @var string
     */
    protected $demoDir;

    /**
     * @inheritdoc
     * @param string $demoDir
     */
    public function __construct(ContainerInterface $container, $entityClass, $demoDir)
    {
        parent::__construct($container, $entityClass);

        $this->demoDir = $demoDir;
    }

    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $options = [])
    {
        $query = $this->repository->createQueryBuilder('demo')
            ->orderBy('demo.createTime')
            ->getQuery();

        return static::queryToPager($query, $limit, $page);
    }

    /**
     * @inheritdoc
     */
    public function allBy(array $criteria, $limit = null, $page = null)
    {
        $offset = (null === $page ?: $page * $limit - $limit);

        return $this->repository->findBy($criteria, null, $limit, $offset);
    }

    /**
     * @inheritdoc
     * @return Demo
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @inheritdoc
     * @return Demo
     */
    public function getBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * @inheritdoc
     * @return Demo
     */
    public function create(Request $request)
    {
        $entity = new $this->entityClass;

        $this->processForm($entity, $request);

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, Request $request)
    {
        /** @var Demo $entity */
        $entity->setFile(new File($this->demoDir . '/' . $entity->getFile()));

        $this->processForm($entity, $request);
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
        $this->submitForm(DemoForm::class, $entity, $request);
        $this->postSubmit($entity);

        parent::persist($entity);
    }

    /**
     * @param EntityInterface $entity
     */
    protected function postSubmit(EntityInterface $entity)
    {
        /** @var Demo $entity */
        $file = $entity->getFile();
        $fileName = $file->getFilename();

        $file->move($this->demoDir, $fileName);
        $entity->setFile($fileName);
    }
}
