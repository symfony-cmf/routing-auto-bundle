<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface PathExistsInterface
{
    /**
     * Initialize with config options
     */
    public function init(array $options);

    public function execute(BuilderContext $builderContext);
}
