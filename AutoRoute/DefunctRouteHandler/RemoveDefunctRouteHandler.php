<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandler;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandlerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContextCollection;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\AdapterInterface;

class RemoveDefunctRouteHandler implements DefunctRouteHandlerInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

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
