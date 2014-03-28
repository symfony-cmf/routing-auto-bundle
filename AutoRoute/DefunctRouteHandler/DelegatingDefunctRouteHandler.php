<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DelegatingRouteHandler;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContextStack;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\MetadataFactory;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\AdapterInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\ServiceRegistry;

/**
 * Defunct route handler which delegates the handling of
 * defunct routes based on the mapped classes confiugration
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class DelegatingRouteHandler implements DefunctRouteHandlerInterface
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
        $realClassName = $this->driver->getRealClassName(get_class($urlContext->getSubjectObject()));
        $metadata = $this->metadataFactory->getMetadataForClass($realClassName);
        list($name, $options) = $metadata->getDefunctRouteHandler();

        $defunctHandler = $this->serviceRegistry->getDefunctRouteHandler($name);

        $referrerCollection = $this->adapter->getReferringRoutes();

        foreach ($referrerCollection as $referrer) {
            if (false === $urlContextStack->containsRoute($referrer)) {
                $urlContexts = $urlContextStack->getUrlContexts();
                
                foreach ($urlContexts as $urlContext) {
                    $newRoute = $urlContext->getNewRoute();
                    $this->adapter->removeDefunctRoute($referrer, $newRoute);
                }
            }
        }
    }
}
