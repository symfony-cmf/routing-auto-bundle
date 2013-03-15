<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface BuilderUnitInterface
{
    public function pathAction(BuilderContext $builderContext);

    public function existsAction(BuilderContext $builderContext);

    public function notExistsAction(BuilderContext $builderContext);
}
