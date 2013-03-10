<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface PathNotExistsInterface
{
    /**
     * Initialize with config options
     */
    public function init(array $options);

    public function execute(BuilderContext $builderContext);
}
