<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;

class OperationStack
{
    protected $persistStack = array();

    public function pushNewRoute(RouteObjectInterface $route)
    {
        $this->persistStack[] = $route;
    }

    public function getPersistStack()
    {
        return $this->persistStack;
    }

    public function containsRoute(RouteObjectInterface $targetRoute)
    {
        foreach ($this->persistStack as $route) {
            if ($route === $targetRoute) {
                return true;
            }
        }

        return false;
    }
}
