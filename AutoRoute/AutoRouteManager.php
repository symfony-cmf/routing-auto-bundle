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
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\AdapterInterface;
use Metadata\MetadataFactoryInterface;

/**
 * This class is concerned with the automatic creation of route objects.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteManager
{
    protected $adapter;
    protected $urlGenerator;
    protected $defunctRouteHandler;

    private $defunctRouteStack = array();

    /**
     * @param AdapterInterface             $adapter             Database adapter
     * @param UrlGeneratorInterface        $urlGenerator        Routing auto URL generator
     * @param DefunctRouteHandlerInterface $defunctRouteHandler Handler for defunct routes 
     */
    public function __construct(
        AdapterInterface $adapter,
        UrlGeneratorInterface $urlGenerator,
        DefunctRouteHandlerInterface $defunctRouteHandler
    )
    {
        $this->adapter = $adapter;
        $this->urlGenerator = $urlGenerator;
        $this->defunctRouteHandler = $defunctRouteHandler;
    }

    /**
     * @param object $document
     */
    public function buildOperationStack(OperationStack $operationStack, $document)
    {
        $urls = $this->getUrlsForDocument($document);

        foreach ($urls as $url) {
            $existingRoute = $this->adapter->findRouteForUrl($url);

            if ($existingRoute) {
                $isSameContent = $this->adapter->compareRouteContent($existingRoute, $document);

                if ($isSameContent) {
                    continue;
                }

                $url = $this->urlGenerator->resolveConflict($document, $url);
            }

            $newRoute = $this->adapter->createRoute($url, $document);
            $operationStack->pushNewRoute($newRoute);
        }

        $this->defunctRouteStack[] = array($document, $operationStack);

        // do we really need the operation stack now? We can just persist...
        return $operationStack;
    }

    public function handleDefunctRoutes()
    {
        while ($defunctRoute = array_pop($this->defunctRouteStack)) {
            list ($document, $operationStack) = $defunctRoute;
            $this->defunctRouteHandler->handleDefunctRoutes($document, $operationStack);
        }
    }

    private function getUrlsForDocument($document)
    {
        $urls = array();
        $locales = $this->adapter->getLocales($document) ? : array(null);

        foreach ($locales as $locale) {
            if (null !== $locale) {
                $this->adapter->translateObject($document, $locale);
            }

            $urls[] = $this->urlGenerator->generateUrl($document);
        }

        return $urls;
    }
}
