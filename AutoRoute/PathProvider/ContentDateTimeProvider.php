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

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Provides path elements by determining them from
 * a DateTime object provided by a designated method on
 * the content object..
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ContentDateTimeProvider extends ContentMethodProvider
{
    public function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'date_format' => 'Y-m-d',
        ));
    }

    public function providePath(RouteStack $routeStack, array $options)
    {
        $object = $routeStack->getContext()->getContent();
        $method = $options['method'];

        if (!method_exists($object, $method)) {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist on class "%s"', $method, get_class($object)));
        }

        $date = $object->$method();

        if (!$date instanceof \DateTime) {
            throw new \RuntimeException(sprintf('Method %s:%s must return an instance of DateTime.',
                get_class($object),
                $method
            ));
        }

        $string = $date->format($options['date_format']);
        $pathElements = $this->normalizePathElements($string, $object, $options['slugify']);

        $routeStack->addPathElements($pathElements);
    }

    /**
     * {@inheritDoc}
     */
    public function normalizePathElements($elements, $object, $slugify = true)
    {
        return parent::normalizePathElements(explode('/', $elements), $object, $slugify);
    }
}
