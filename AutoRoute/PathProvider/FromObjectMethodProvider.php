<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathProvider;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathProviderInterface;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\Exception\MissingOptionException;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class FromObjectMethodProvider implements PathProviderInterface
{
    protected $method;

    public function init(array $options)
    {
        if (!isset($options['method'])) {
            throw new MissingOptionException(__CLASS__, 'method');
        }

        $this->method = $options['method'];
    }

    public function providePath(BuilderContext $context)
    {
        $object = $context->getObject();
        $method = $this->method;

        if (!method_exists($object, $method)) {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist on class "%s"', $method, get_class($object)));
        }

        $path = $object->$method();

        // @todo: Validate the validator service.

        $context->addPath($path);
    }
}

