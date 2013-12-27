<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Driver;

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

    public function translateObject($object, $locale);
}
