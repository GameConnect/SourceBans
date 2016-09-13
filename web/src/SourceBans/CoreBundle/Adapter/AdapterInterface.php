<?php

namespace SourceBans\CoreBundle\Adapter;

use SourceBans\CoreBundle\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * AdapterInterface
 */
interface AdapterInterface
{
    /**
     * Get a collection of entities
     * @param integer $limit
     * @param integer $page
     * @param string $sort
     * @param string $order
     * @param array $criteria
     * @return \IteratorAggregate
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $criteria = []);

    /**
     * Get a collection of entities
     * @param array $criteria
     * @param integer $limit
     * @param integer $page
     * @return \IteratorAggregate
     */
    public function allBy(array $criteria, $limit = null, $page = null);

    /**
     * Get one entity
     * @param integer $id
     * @return EntityInterface
     */
    public function get($id);

    /**
     * Get one entity
     * @param array $criteria
     * @return EntityInterface
     */
    public function getBy(array $criteria);

    /**
     * Get one entity or throw a 404 exception
     * @param integer $id
     * @return EntityInterface
     * @throws NotFoundHttpException
     */
    public function getOr404($id);

    /**
     * Create an entity
     * @param Request $request
     * @return EntityInterface
     */
    public function create(Request $request);

    /**
     * Update an entity
     * @param EntityInterface $entity
     * @param Request $request
     */
    public function update(EntityInterface $entity, Request $request);

    /**
     * Delete an entity
     * @param EntityInterface $entity
     */
    public function delete(EntityInterface $entity);

    /**
     * Persist an entity
     * @param EntityInterface $entity
     */
    public function persist(EntityInterface $entity);
}
