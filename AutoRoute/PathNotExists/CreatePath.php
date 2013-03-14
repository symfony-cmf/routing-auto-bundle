<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathNotExists;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathActionInterface;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\RouteMakerInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class CreatePath implements PathActionInterface
{
    protected $routeMaker;

    public function __construct(RouteMakerInterface $routeMaker)
    {
        $this->routeMaker = $routeMaker;
    }

    public function init(array $options)
    {
    }

    public function execute(BuilderContext $context)
    {
        $this->routeMaker->makeRoutes($context);
    }
}
