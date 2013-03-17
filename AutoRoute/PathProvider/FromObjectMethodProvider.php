<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\MissingOptionException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

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

    public function providePath(RouteStack $routeStack)
    {
        $object = $routeStack->getContext()->getContent();
        $method = $this->method;

        if (!method_exists($object, $method)) {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist on class "%s"', $method, get_class($object)));
        }

        $pathElements = $object->$method();

        if (!is_array($pathElements)) {
            throw new \RuntimeException(sprintf(
                'FromObjectMethodProvider wants %s:%s to return an array of route names.. got "%s"',
                get_class($object),
                $method,
                gettype($pathElements)
            ));
        }

        // @todo: Validate the validator service.

        $routeStack->addPathElements($pathElements);
    }
}

