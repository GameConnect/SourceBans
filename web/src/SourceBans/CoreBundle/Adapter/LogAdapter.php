<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Condition;
use Rb\Specification\Doctrine\Logic\AndX;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\Log;
use SourceBans\CoreBundle\Exception\InvalidFormException;
use SourceBans\CoreBundle\Form\LogForm;
use SourceBans\CoreBundle\Specification\ById;
use SourceBans\CoreBundle\Specification\LogSpecification;

/**
 * LogAdapter
 */
class LogAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $options = [])
    {
        $specification = new LogSpecification;

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function allBy(array $criteria, $limit = null, $page = null)
    {
        $specification = new LogSpecification;
        foreach ($criteria as $field => $value) {
            $specification->add(new Condition\Equals($field, $value));
        }

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Log
     */
    public function get($id)
    {
        $specification = new AndX(
            new LogSpecification,
            new ById($id)
        );

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Log
     */
    public function getBy(array $criteria)
    {
        $specification = new LogSpecification;
        foreach ($criteria as $field => $value) {
            $specification->add(new Condition\Equals($field, $value));
        }

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     * @return Log
     */
    public function create(array $parameters = null)
    {
        /** @var Log $entity */
        $entity = new $this->entityClass;
        $entity->setAdmin($this->container->get('security.token_storage')->getToken()->getUser());
        $entity->setAdminIp($this->container->get('request')->getClientIp());
        $entity->setFunction($this->getBacktrace());
        $entity->setQuery($this->container->get('request')->getQueryString());

        $this->processForm($entity, $parameters);

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, array $parameters = null)
    {
        $this->processForm($entity, $parameters);
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
    }

    /**
     * @param EntityInterface $entity
     * @param array $parameters
     * @throws InvalidFormException
     */
    protected function processForm(EntityInterface $entity, array $parameters = null)
    {
        $this->submitForm(LogForm::class, $entity, $parameters);

        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }

    /**
     * @param integer $limit
     * @return string
     */
    private function getBacktrace($limit = 5)
    {
        $traces = array_slice(debug_backtrace(), 3); // Strip first 3 traces
        $count  = 0;
        $ret    = '';

        foreach ($traces as $trace) {
            if (!isset($trace['file'], $trace['line'])) {
                continue;
            }

            $ret .= $trace['file'] . ' (' . $trace['line'] . ")\n";

            if (++$count >= $limit) {
                break;
            }
        }

        return trim($ret);
    }
}
