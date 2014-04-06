<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandler;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandlerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContextStack;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\AdapterInterface;

class LeaveRedirectDefunctRouteHandler implements DefunctRouteHandlerInterface
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
    public function handleDefunctRoutes(UrlContextStack $urlContextStack)
    {
        $referringAutoRouteCollection = $this->adapter->getReferringAutoRoutes($urlContextStack->getSubjectObject());

        foreach ($referringAutoRouteCollection as $referringAutoRoute) {
            if (false === $urlContextStack->containsAutoRoute($referringAutoRoute)) {
                $newRoute = $urlContextStack->getAutoRouteByTag($referringAutoRoute->getAutoRouteTag());

                $this->adapter->migrateAutoRouteChildren($referringAutoRoute, $newRoute);
                $this->adapter->removeAutoRoute($referringAutoRoute);
                $this->adapter->createRedirectRoute($url, $newRoute);
            }
        }
    }
}

