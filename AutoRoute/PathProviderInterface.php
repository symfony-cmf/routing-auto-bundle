<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface PathProviderInterface
{
    /**
     * Provide a URL
     *
     * @return string
     */
    public function providePath(BuilderContext $builderContext);
}
