<?php

namespace SourceBans\CoreBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use SourceBans\CoreBundle\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * AdapterParamConverter
 */
class AdapterParamConverter implements ParamConverterInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $servicePrefix;

    /**
     * @var array
     */
    private $supportedClasses = [];

    /**
     * @param ContainerInterface $container
     * @param string $servicePrefix
     * @param array $supportedClasses
     */
    public function __construct(ContainerInterface $container, $servicePrefix = null, array $supportedClasses = [])
    {
        $this->container = $container;
        $this->servicePrefix = $servicePrefix;
        $this->supportedClasses = $supportedClasses;
    }

    /**
     * @inheritdoc
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $id = $request->attributes->get('id');
        $name = $configuration->getName();

        /** @var AdapterInterface $adapter */
        $adapter = $this->container->get($this->servicePrefix . $name);
        $entity = $adapter->getOr404($id);
        $request->attributes->set($name, $entity);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function supports(ParamConverter $configuration)
    {
        return in_array($configuration->getClass(), $this->supportedClasses);
    }
}
