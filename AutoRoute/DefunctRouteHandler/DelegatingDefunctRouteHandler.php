<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandler;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\MetadataFactory;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\AdapterInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\ServiceRegistry;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandlerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContextStack;

/**
 * Defunct route handler which delegates the handling of
 * defunct routes based on the mapped classes confiugration
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class DelegatingDefunctRouteHandler implements DefunctRouteHandlerInterface
{
    protected $serviceRegistry;
    protected $adapter;

    /**
     * @param ServiceRegistry auto routing service registry (for getting old route action)
     * @param AdapterInterface auto routing backend adapter
     * @param MetadataFactory  auto routing metadata factory
     */
    public function __construct(
        MetadataFactory $metadataFactory,
        AdapterInterface $adapter,
        ServiceRegistry $serviceRegistry
    )
    {
        $this->serviceRegistry = $serviceRegistry;
        $this->adapter = $adapter;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function handleDefunctRoutes(UrlContextStack $urlContextStack)
    {
        $subject = $urlContextStack->getSubjectObject();
        $realClassName = $this->adapter->getRealClassName(get_class($urlContextStack->getSubjectObject()));
        $metadata = $this->metadataFactory->getMetadataForClass($realClassName);

        var_dump($metadata);die();

        list($name, $options) = $metadata->getDefunctRouteHandler();

        $defunctHandler = $this->serviceRegistry->getDefunctRouteHandler($name);
        $defunctHandler->handleDefunctRoutes($urlContextStack);
    }
}
