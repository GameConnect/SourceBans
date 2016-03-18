<?php

namespace SourceBans\CoreBundle\Adapter;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\SpecificationRepository;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * AbstractAdapter
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var SpecificationRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @param ContainerInterface $container
     * @param string $entityClass
     */
    public function __construct(ContainerInterface $container, $entityClass)
    {
        $this->container = $container;
        $this->dispatcher = $container->get('event_dispatcher');
        $this->formFactory = $container->get('form.factory');
        $this->objectManager = $container->get('doctrine')->getManagerForClass($entityClass);
        $this->repository = $this->objectManager->getRepository($entityClass);
        $this->entityClass = $this->repository->getClassName();
    }

    /**
     * Get one entity or throw a 404 exception
     * @param integer $id
     * @return EntityInterface
     * @throws NotFoundHttpException
     */
    public function getOr404($id)
    {
        $resource = $this->get($id);
        if ($resource === null) {
            throw new NotFoundHttpException(sprintf('The resource with ID %d was not found.', $id));
        }

        return $resource;
    }

    /**
     * Delete an entity
     * @param EntityInterface $entity
     */
    public function delete(EntityInterface $entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
    }

    /**
     * Persist an entity
     * @param EntityInterface $entity
     */
    public function persist(EntityInterface $entity)
    {
        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }

    /**
     * @param ObjectManager $objectManager
     * @return AbstractAdapter
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;

        return $this;
    }

    /**
     * @param FormFactoryInterface $formFactory
     * @return AbstractAdapter
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;

        return $this;
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     * @return AbstractAdapter
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * @param string|FormTypeInterface $type
     * @param EntityInterface $entity
     * @param Request $request
     * @return FormInterface
     * @throws InvalidFormException
     */
    protected function submitForm($type, EntityInterface $entity, Request $request)
    {
        $form = $this->formFactory->create($type, $entity)
            ->handleRequest($request);

        if (!$form->isSubmitted()) {
            throw new InvalidFormException('No data submitted', $form);
        }
        if (!$form->isValid()) {
            throw new InvalidFormException('Invalid submitted data', $form);
        }

        return $form;
    }

    /**
     * @param Query $query
     * @param integer $limit
     * @param integer $page
     * @return array|Pagerfanta
     */
    protected static function queryToPager(Query $query, $limit = null, $page = null)
    {
        if ($limit === null) {
            return $query->getResult();
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($query));

        return $pager->setMaxPerPage($limit)->setCurrentPage($page ?: 1);
    }
}
