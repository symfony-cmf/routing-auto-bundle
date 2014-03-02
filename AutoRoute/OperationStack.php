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
}
