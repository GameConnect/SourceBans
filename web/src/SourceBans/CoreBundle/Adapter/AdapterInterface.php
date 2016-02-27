<?php

namespace SourceBans\CoreBundle\Adapter;

use SourceBans\CoreBundle\Entity\EntityInterface;
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
     * @param array $options
     * @return \IteratorAggregate
     */
    public function all($limit = 25, $page = 1, $sort = null, $order = null, array $options = []);

    /**
     * Get one entity
     * @param integer $id
     * @return EntityInterface
     */
    public function get($id);

    /**
     * Get one entity or throw a 404 exception
     * @param integer $id
     * @return EntityInterface
     * @throws NotFoundHttpException
     */
    public function getOr404($id);

    /**
     * Create an entity
     * @param array $parameters
     * @return EntityInterface
     */
    public function create(array $parameters);

    /**
     * Update an entity
     * @param EntityInterface $entity
     * @param array $parameters
     */
    public function update(EntityInterface $entity, array $parameters);

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
