<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\MissingOptionException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class FromObjectMethodProvider implements PathProviderInterface
{
    protected $method;
    protected $slugifier;
    protected $slugify;

    public function __construct(SlugifierInterface $slugifier)
    {
        $this->slugifier = $slugifier;
    }

    public function init(array $options)
    {
        if (!isset($options['method'])) {
            throw new MissingOptionException(__CLASS__, 'method');
        }

        $options = array_merge(array(
            'slugify' => true
        ), $options);

        $this->method = $options['method'];
        $this->slugify = $options['slugify'];
    }

    public function providePath(RouteStack $routeStack)
    {
        $object = $routeStack->getContext()->getContent();
        $method = $this->method;

        if (!method_exists($object, $method)) {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist on class "%s"', $method, get_class($object)));
        }

        $pathElements = $object->$method();

        if (is_string($pathElements)) {
            if (substr($pathElements, 0, 1) == '/') {
                throw new \RuntimeException('Path must not be absolute.');
            }

            $pathElements = explode('/', $pathElements);
        }

        if (!is_array($pathElements)) {
            throw new \RuntimeException(sprintf(
                'FromObjectMethodProvider wants %s:%s to return an array of route names.. got "%s"',
                get_class($object),
                $method,
                gettype($pathElements)
            ));
        }

        if (true === $this->slugify) {
            $slugifier = $this->slugifier;
            array_walk($pathElements, function (&$pathElement) use ($slugifier) {
                $pathElement = $slugifier->slugify($pathElement);
            });
        }

        // @todo: Validate the validator service.
        $routeStack->addPathElements($pathElements);
    }
}
