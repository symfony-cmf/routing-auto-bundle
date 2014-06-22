<?php

namespace Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandler;

use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface;
use Symfony\Cmf\Component\RoutingAuto\UrlContextCollection;
use Symfony\Cmf\Component\RoutingAuto\Adapter\AdapterInterface;

class RemoveDefunctRouteHandler implements DefunctRouteHandlerInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @param AdapterInterface
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritDoc}
     */
    public function handleDefunctRoutes(UrlContextCollection $urlContextCollection)
    {
        $referringAutoRouteCollection = $this->adapter->getReferringAutoRoutes($urlContextCollection->getSubjectObject());

        foreach ($referringAutoRouteCollection as $referringAutoRoute) {
            if (false === $urlContextCollection->containsAutoRoute($referringAutoRoute)) {
                $newRoute = $urlContextCollection->getAutoRouteByTag($referringAutoRoute->getAutoRouteTag());

                $this->adapter->migrateAutoRouteChildren($referringAutoRoute, $newRoute);
                $this->adapter->removeAutoRoute($referringAutoRoute);
            }
        }
    }
}
