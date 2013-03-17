<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathNotExists;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RoutePatcherInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class CreatePath implements PathActionInterface
{
    protected $routePatcher;

    public function __construct(RoutePatcherInterface $routePatcher)
    {
        $this->routePatcher = $routePatcher;
    }

    public function init(array $options)
    {
    }

    public function execute(RouteStack $routeStack, BuilderContext $context)
    {
        $this->routePatcher->makeRoutes($routeStack, $context);
    }
}
