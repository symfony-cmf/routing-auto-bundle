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
 * a DateTime object provided by a designated method on
 * the content object..
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ContentDateTimeProvider extends ContentMethodProvider
{
    protected $dateFormat;

    public function init(array $options)
    {
        parent::init($options);

        $options = array_merge(array(
            'date_format' => 'Y-m-d'
        ), $options);

        $this->dateFormat = $options['date_format'];
    }

    public function providePath(RouteStack $routeStack)
    {
        $object = $routeStack->getContext()->getContent();
        $method = $this->method;

        if (!method_exists($object, $method)) {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist on class "%s"', $method, get_class($object)));
        }

        $date = $object->$method();

        if (!$date instanceof \DateTime) {
            throw new \RuntimeException(sprintf('Method %s:%s must return an instance of DateTime.'));
        }

        $string = $date->format($this->dateFormat);
        $pathElements = $this->normalizePathElements($string, $object);

        $routeStack->addPathElements($pathElements);
    }

    /**
     * {@inheritDoc}
     */
    public function normalizePathElements($elements, $object)
    {
        return parent::normalizePathElements(explode('/', $elements), $object);
    }
}
