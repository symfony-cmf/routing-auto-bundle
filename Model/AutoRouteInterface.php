<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Model;

/**
 * Interface to be implemented by objects which represent
 * auto routes.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface AutoRouteInterface
{
    /**
     * Set a tag which can be used by a database implementation
     * to distinguish a route from other routes as required
     *
     * @param string $tag
     */
    public function setAutoRouteTag($tag);

    /**
     * Return the auto route tag
     *
     * @return string
     */
    public function getAutoRouteTag();
}
