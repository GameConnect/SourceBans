<?php

namespace SourceBans\CoreBundle\Adapter;

use Pagerfanta\Pagerfanta;
use Rb\Specification\Doctrine\Logic;
use Rb\Specification\Doctrine\Query;
use SourceBans\CoreBundle\Entity\EntityInterface;
use SourceBans\CoreBundle\Entity\Log;
use SourceBans\CoreBundle\Specification\ById;
use SourceBans\CoreBundle\Specification\LogSpecification;
use Symfony\Component\HttpFoundation\Request;

/**
 * LogAdapter
 */
class LogAdapter extends AbstractAdapter
{
    /**
     * @inheritdoc
     * @return Pagerfanta
     */
    public function all($limit = null, $page = null, $sort = null, $order = null, array $criteria = [])
    {
        $specification = new Logic\AndX(
            new LogSpecification,
            new Query\OrderBy($sort ?: 'createTime', $order)
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
        $specification = new LogSpecification;
        array_map([$specification, 'add'], $criteria);

        return static::queryToPager($this->repository->match($specification), $limit, $page);
    }

    /**
     * @inheritdoc
     * @return Log
     */
    public function get($id)
    {
        $specification = new Logic\AndX(
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
        array_map([$specification, 'add'], $criteria);

        return $this->repository->match($specification)->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function create(Request $request)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function update(EntityInterface $entity, Request $request)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function persist(EntityInterface $entity)
    {
        /** @var Log $entity */
        $entity->setAdmin($this->container->get('security.token_storage')->getToken()->getUser());
        $entity->setAdminIp($this->container->get('request_stack')->getCurrentRequest()->getClientIp());
        $entity->setFunction($this->getBacktrace());
        $entity->setQuery($this->container->get('request_stack')->getCurrentRequest()->getQueryString());

        parent::persist($entity);
    }

    /**
     * @return string
     */
    private function getBacktrace()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $traces    = [];

        foreach ($backtrace as $trace) {
            if (!isset($trace['file'], $trace['line'])) {
                continue;
            }

            $traces[] = $trace['file'] . ':' . $trace['line'];
        }

        return implode("\n", $traces);
    }
}
