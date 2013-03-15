<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathNotExists;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\CouldNotFindRouteException;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ThrowException implements PathActionInterface
{
    protected $routeMaker;

    public function __construct()
    {
    }

    public function init(array $options)
    {
    }

    public function execute(BuilderContext $context)
    {
        throw new CouldNotFindRouteException($context->getPath());
    }
}

