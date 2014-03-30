<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRouteInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext;

/**
 * Adapters will (eventually) abstract all database operations
 * with the aim of enabling other providers such as ORM.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface AdapterInterface
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
     * @param string $tag
     *
     * @return AutoRouteInterface new route document
     */
    public function createRoute($path, $document, $tag);

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

    /**
     * Generate a tag which can be used to identify this route from
     * other routes as required.
     *
     * @param UrlContext $urlContext
     */
    public function generateAutoRouteTag(UrlContext $urlContext);

    /**
     * Migrate the descendant path elements from one route to another.
     *
     * e.g. in an RDBMS with a routes:
     *
     *    /my-blog 
     *    /my-blog/posts/post1 
     *    /my-blog/posts/post2
     *    /my-new-blog
     *
     * We want to migrate the children of "my-blog" to "my-new-blog" so that
     * we have:
     *
     *    /my-blog 
     *    /my-new-blog
     *    /my-new-blog/posts/post1 
     *    /my-new-blog/posts/post2
     *
     * @param AutoRouteInterface $srcAutoRoute
     * @param AutoRouteInterface $destAutoRoute
     */
    public function migrateAutoRouteChildren(AutoRouteInterface $srcAutoRoute, AutoRouteInterface $destAutoRoute);

    /**
     * Remove the given auto route
     *
     * @param AutoRouteInterface $autoRoute
     */
    public function removeAutoRoute(AutoRouteInterface $autoRoute);
}
