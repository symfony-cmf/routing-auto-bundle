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

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\MissingOptionException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
class ContentMethodProvider extends AbstractPathProvider
{
    protected $slugifier;

    public function __construct(SlugifierInterface $slugifier)
    {
        $this->slugifier = $slugifier;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'slugify' => true,
        ));

        $resolver->setRequired(array('method'));

        $resolver->setAllowedTypes(array(
            'slugify' => 'bool',
        ));
    }

    public function providePath(RouteStack $routeStack, array $options)
    {
        $object = $routeStack->getContext()->getContent();
        $method = $options['method'];

        if (!method_exists($object, $method)) {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist on class "%s"', $method, get_class($object)));
        }

        $pathElements = $object->$method();
        $pathElements = $this->normalizePathElements($pathElements, $object, $options['slugify']);

        // @todo: Validate the validator service.
        $routeStack->addPathElements($pathElements);
    }

    /**
     * Normalize the given $pathElements variable to an array of path elements,
     * accepting either an array or a string.
     *
     * A string will be converted to an array of elements delimiteed by the
     * path separator.
     *
     * If slugify is enabled, each path element will be slugified.
     *
     * @param mixed  $pathElements Either an array or a string
     * @param object $object       Used in the case of an exception
     *
     * @return array
     */
    protected function normalizePathElements($pathElements, $object, $slugify = true)
    {
        if (is_string($pathElements) || (is_object($pathElements) && method_exists($pathElements, '__toString'))) {
            if (substr($pathElements, 0, 1) == '/') {
                throw new \RuntimeException('Path must not be absolute.');
            }

            $pathElements = array($pathElements);
        }

        if (!is_array($pathElements)) {
            throw new \RuntimeException(sprintf(
                'FromObjectMethodProvider wants %s::%s to return an array of route names or a string, got "%s"',
                get_class($object),
                $this->method,
                gettype($pathElements)
            ));
        }

        if ($slugify) {
            $slugifier = $this->slugifier;
            array_walk($pathElements, function (&$pathElement) use ($slugifier) {
                $pathElement = $slugifier->slugify($pathElement);
            });
        }

        return $pathElements;
    }
}
