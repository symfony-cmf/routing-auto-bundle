<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\MissingOptionException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;

/**
 * Provides path elements by determining them from
 * the return value of a method on the Content object.
 *
 * The path elements returned by the designated method can
 * either be a string of path elements delimited by the path
 * separator "/" or an array of path elements:
 *
 *  - a/full/path
 *  - array('a', 'full', 'path')
 *
 * Each element will be automatically slugified unless the
 * slugify option is explicitly set to false.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ContentMethodProvider implements PathProviderInterface
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

        $pathElements = $this->normalizePathElements($pathElements, get_class($object).'::'.$method);


        // @todo: Validate the validator service.
        $routeStack->addPathElements($pathElements);
    }

    protected function normalizePathElements($pathElements, $methodAsString)
    {
        if (is_string($pathElements) || (is_object($pathElements) && method_exists($pathElements, '__toString'))) {
            if (substr($pathElements, 0, 1) == '/') {
                throw new \RuntimeException('Path must not be absolute.');
            }

            $pathElements = array($pathElements);
        }

        if (!is_array($pathElements)) {
            throw new \RuntimeException(sprintf(
                'FromObjectMethodProvider wants %s to return an array of route names or a string, got "%s"',
                $methodAsString,
                gettype($pathElements)
            ));
        }

        if (true === $this->slugify) {
            $slugifier = $this->slugifier;
            array_walk($pathElements, function (&$pathElement) use ($slugifier) {
                $pathElement = $slugifier->slugify($pathElement);
            });
        }

        return $pathElements;
    }
}
