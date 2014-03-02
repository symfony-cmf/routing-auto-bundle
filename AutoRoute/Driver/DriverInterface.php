<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Driver;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Drivers will (eventually) abstract all database operations
 * with the aim of enabling other providers such as ORM.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface DriverInterface
{
    /**
     * Return locales for object
     *
     * @return array
     */
    public function getLocales($object);

    /**
     * Translate the given object into the given locale
     *
     * @param object $object
     * @param string $locale  e.g. fr, en, de, be, etc.
     */
    public function translateObject($object, $locale);

    /**
     * Create a new auto route at the given path
     * with the given document as the content.
     *
     * @param string $path
     * @param object $document
     *
     * @return Route  new route document
     */
    public function createRoute($path, $document);

    /**
     * Return the canonical name for the given class, this is 
     * required as somethimes an ORM may return a proxy class.
     *
     * @return string
     */
    public function getRealClassName($className);

    /**
     * Return true if the content associated with the route
     * and the given content object are the same.
     *
     * @param RouteObjectInterface
     * @param object
     */
    public function compareRouteContent(RouteObjectInterface $route, $contentObject);

    /**
     * Attempt to find a route with the given URL
     *
     * @param string $url
     *
     * @return null|Symfony\Cmf\Component\Routing\RouteObjectInterface
     */
    public function findRouteForUrl($url);
}
