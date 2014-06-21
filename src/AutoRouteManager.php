<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\AutoRoute;

use Symfony\Cmf\Component\RoutingAuto\AutoRoute\Adapter\AdapterInterface;

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

    private $pendingUrlContextCollections = array();

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
    public function buildUrlContextCollection(UrlContextCollection $urlContextCollection)
    {
        $this->getUrlContextsForDocument($urlContextCollection);

        foreach ($urlContextCollection->getUrlContexts() as $urlContext) {
            $existingRoute = $this->adapter->findRouteForUrl($urlContext->getUrl());

            $autoRoute = null;

            if ($existingRoute) {
                $isSameContent = $this->adapter->compareAutoRouteContent($existingRoute, $urlContext->getSubjectObject());

                if ($isSameContent) {
                    $autoRoute = $existingRoute;
                } else {
                    $url = $urlContext->getUrl();
                    $url = $this->urlGenerator->resolveConflict($urlContext);
                    $urlContext->setUrl($url);
                }
            }

            if (!$autoRoute) {
                $autoRouteTag = $this->adapter->generateAutoRouteTag($urlContext);
                $autoRoute = $this->adapter->createAutoRoute($urlContext->getUrl(), $urlContext->getSubjectObject(), $autoRouteTag);
            }

            $urlContext->setAutoRoute($autoRoute);
        }

        $this->pendingUrlContextCollections[] = $urlContextCollection;
    }

    public function handleDefunctRoutes()
    {
        while ($urlContextCollection = array_pop($this->pendingUrlContextCollections)) {
            $this->defunctRouteHandler->handleDefunctRoutes($urlContextCollection);
        }
    }

    /**
     * Populates an empty UrlContextCollection with UrlContexts
     *
     * @param $urlContextCollection UrlContextCollection
     */
    private function getUrlContextsForDocument(UrlContextCollection $urlContextCollection)
    {
        $locales = $this->adapter->getLocales($urlContextCollection->getSubjectObject()) ? : array(null);

        foreach ($locales as $locale) {
            if (null !== $locale) {
                $this->adapter->translateObject($urlContextCollection->getSubjectObject(), $locale);
            }

            // create and add url context to stack
            $urlContext = $urlContextCollection->createUrlContext($locale);
            $urlContextCollection->addUrlContext($urlContext);

            // generate the URL
            $url = $this->urlGenerator->generateUrl($urlContext);

            // update the context with the URL
            $urlContext->setUrl($url);
        }
    }
}
