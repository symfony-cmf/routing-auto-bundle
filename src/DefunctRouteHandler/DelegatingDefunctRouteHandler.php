<?php

namespace Symfony\Cmf\Component\RoutingAuto\AutoRoute\DefunctRouteHandler;

use Symfony\Cmf\Component\RoutingAuto\AutoRoute\Mapping\MetadataFactory;
use Symfony\Cmf\Component\RoutingAuto\AutoRoute\Adapter\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\AutoRoute\ServiceRegistry;
use Symfony\Cmf\Component\RoutingAuto\AutoRoute\DefunctRouteHandlerInterface;
use Symfony\Cmf\Component\RoutingAuto\AutoRoute\UrlContextCollection;

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
    public function handleDefunctRoutes(UrlContextCollection $urlContextCollection)
    {
        $subject = $urlContextCollection->getSubjectObject();
        $realClassName = $this->adapter->getRealClassName(get_class($urlContextCollection->getSubjectObject()));
        $metadata = $this->metadataFactory->getMetadataForClass($realClassName);

        $defunctRouteHandlerConfig = $metadata->getDefunctRouteHandler();

        $defunctHandler = $this->serviceRegistry->getDefunctRouteHandler($defunctRouteHandlerConfig['name']);
        $defunctHandler->handleDefunctRoutes($urlContextCollection);
    }
}
