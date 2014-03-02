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

use Doctrine\Common\Util\ClassUtils;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Driver\DriverInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\MappingFactoryInterface;

/**
 * This class is concerned with the automatic creation of route objects.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteManager
{
    protected $driver;
    protected $mappingFactory;
    protected $urlGenerator;
    protected $defunctRouteHandler;

    /**
     * @param DriverInterface $driver  Database driver
     * @param MappingFactory $mappingFactory  Mappig factory
     */
    public function __construct(
        DriverInterface $driver,
        MappingFactoryInterface $mappingFactory,
        UrlGeneratorInterface $urlGenerator,
        DefunctRouteHandlerInterface $defunctRouteHandler
    )
    {
        $this->mappingFactory = $mappingFactory;
        $this->driver = $driver;
        $this->urlGenerator = $urlGenerator;
        $this->defunctRouteHandler = $defunctRouteHandler;
    }

    /**
     * @param object $document
     */
    public function buildOperationStack($operationStack, $document)
    {
        $urls = $this->getUrlsForDocument($document);

        foreach ($urls as $url) {
            $existingRoute = $this->driver->findRouteForUrl($url);

            if ($existingRoute) {
                $isSameContent = $this->driver->compareRouteContent($existingRoute, $document);

                if ($isSameContent) {
                    continue;
                }

                $url = $this->urlGenerator->resolveConflict($document, $url);
            }

            $newRoute = $this->driver->createRoute($url, $document);
            $operationStack->pushNewRoute($newRoute);
        }

        $this->defunctRouteHandler->handleDefunctRoutes($document, $operationStack);

        return $operationStack;
    }

    private function getUrlsForDocument($document)
    {
        $urls = array();
        $locales = $this->driver->getLocales($document) ? : array(null);

        foreach ($locales as $locale) {
            if (null !== $locale) {
                $this->driver->translateObject($document, $locale);
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
