<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface PathActionInterface
{
    /**
     * Initialize with config options
     */
    public function init(array $options);

    public function execute(RouteStack $stack, BuilderContext $builderContext);
}
