<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface PathProviderInterface
{
    /**
     * Initialize with config options
     */
    public function init(array $options);

    /**
     * Provide a URL
     *
     * @return string
     */
    public function providePath(RouteStack $routeStack, BuilderContext $builderContext);
}
