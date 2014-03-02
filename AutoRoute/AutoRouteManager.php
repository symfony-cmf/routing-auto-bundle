<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Driver\DriverInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\MappingFactory;

/**
 * This class is concerned with the automatic creation of route objects.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteManager
{
    protected $factory;
    protected $driver;
    protected $mappingFactory;

    public function __construct(DriverInterface $driver, MappingFactory $mappingFactory, Builder $builder)
    {
        $this->mappingFactory = $mappingFactory;
        $this->builder = $builder;
        $this->driver = $driver;
    }

    /**
     */
    public function getOperationStackForDocument($document)
    {
        $operationStack = new OperationStack();

        $urls = $this->getUrlsForDocument($document);
        $originalRoutes = $this->driver->getReferreringRoutes($document);

        foreach ($urls as $url) {
            $newRoute = null;

            if ($existingRoute = $this->driver->findRoute($url)) {
                $isSameContent = $this->driver->compareRouteContent($existingRoute, $document);

                if ($isSameContent) {
                    continue;
                }

                $url = $this->urlGenerator->resolveConflict($document, $url);
            }

            $newRoute = $this->driver->createRoute($url, $document);
            $operationStack->pushNewRoute($newRoute);
        }

        $this->oldRouteHandler->handleOldRoutes($document, $operationStack);

        return $operationStack;
    }

    private function getUrlsForDocument($document)
    {
        $urls = array();
        $locales = $this->driver->getLocales($document) ? : array(null);

        foreach ($locales as $locale) {
            if (null !== $locale) {
                $document = $this->driver->translateObject($document, $locale);
            }

            $urls[] = $this->urlGenerator->generateUrl($document);
        }

        return $urls;
    }

    /**
     * Return true if the given document is mapped with AutoRoute
     *
     * @param object $document Document
     *
     * @return boolean
     */
    public function isAutoRouteable($document)
    {
        return $this->mappingFactory->hasMapping($this->driver->getRealClassName(get_class($document)));
    }
}
