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
class OldRouteHandler
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

    public function handleOldRoutes($oldRoutes, $document, $operationStack)
    {
        // $this->driver->getReferringRoutesForDocument($document)
        // 1. determine any routes which refer to this document but which are
        //    not in the operation stack
        // 2. take the configured action on that route (delete it, replace it, etc.)
    }
}
