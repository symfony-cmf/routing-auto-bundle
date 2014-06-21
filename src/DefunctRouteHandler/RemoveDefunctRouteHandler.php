<?php

namespace Symfony\Cmf\Component\RoutingAuto\AutoRoute\DefunctRouteHandler;

use Symfony\Cmf\Component\RoutingAuto\AutoRoute\DefunctRouteHandlerInterface;
use Symfony\Cmf\Component\RoutingAuto\AutoRoute\UrlContextCollection;
use Symfony\Cmf\Component\RoutingAuto\AutoRoute\Adapter\AdapterInterface;

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
