<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface BuilderUnitInterface
{
    public function pathAction(BuilderContext $builderContext);

    public function existsAction(BuilderContext $builderContext);

    public function notExistsAction(BuilderContext $builderContext);
}
