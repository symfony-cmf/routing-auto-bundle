<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

/**
 * Class which takes actions on routes which are left behind
 * after a content changes its URL.
 *
 * For examples:
 *
 * - Leave a redirect route
 * - Delete the route
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class DefunctRouteHandler implements DefunctRouteHandlerInterface
{
    protected $serviceRegistry;
    protected $driver;

    /**
     * @param ServiceRegistry auto routing service registry (for getting old route action)
     * @param DriverInterface auto routing backend driver
     * @param MappingFactory  auto routing mapping factory
     */
    public function __consturct(
        ServiceRegistry $serviceRegistry,
        DriverInterface $driver,
        MappingFactory $mappingFactory
    )
    {
        $this->serviceRegistry = $serviceRegistry;
        $this->driver = $driver;
        $this->mappingFactory = $mappingFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function handleDefunctRoutes($oldRoutes, $document, $operationStack)
    {
    }
}
