<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandler;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandlerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContextStack;
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
    public function handleDefunctRoutes(UrlContextStack $urlContextStack)
    {
        $referringRouteCollection = $this->adapter->getReferringRoutes($urlContextStack->getSubjectObject());

        foreach ($referringRouteCollection as $referringRoute) {
            if (false === $urlContextStack->containsRoute($referringRoute)) {
                $newRoute = $urlContextStack->getRouteByTag($referringRoute->getAutoRouteTag());

                $this->adapter->migrateAutoRouteChildren($referringRoute, $newRoute);
                $this->adapter->removeAutoRoute($referringRoute);
            }
        }
    }
}
