<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathProvider;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathProviderInterface;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\Exception\MissingOptionException;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class SpecifiedProvider implements PathProviderInterface
{
    protected $path;

    public function init(array $options)
    {
        if (!isset($options['path'])) {
            throw new MissingOptionException(__CLASS__, 'path');
        }

        $this->path = $options['path'];
    }

    public function providePath(BuilderContext $context)
    {
        $context->addPath($this->path);
    }
}
