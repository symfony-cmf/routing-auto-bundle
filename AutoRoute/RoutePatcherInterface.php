<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

/**
 * Classes implementing this interface "patch" 
 * any missing comonents in/a/routes/path
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface RoutePatcherInterface
{
    public function patch(BuilderContext $context);
}
