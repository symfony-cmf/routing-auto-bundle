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
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext;

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

    private $pendingUrlContextStacks = array();

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
    public function buildUrlContextStack(UrlContextStack $urlContextStack)
    {
        $this->getUrlContextsForDocument($urlContextStack);

        foreach ($urlContextStack->getUrlContexts() as $urlContext) {
            $existingRoute = $this->adapter->findRouteForUrl($urlContext->getUrl());

            $route = null;

            if ($existingRoute) {
                $isSameContent = $this->adapter->compareRouteContent($existingRoute, $urlContext->getSubjectObject());

                if ($isSameContent) {
                    $route = $existingRoute;
                } else {
                    $url = $urlContext->getUrl();
                    $url = $this->urlGenerator->resolveConflict($url);
                    $urlContext->setUrl($url);
                }
            }

            if (!$route) {
                $route = $this->adapter->createRoute($urlContext->getUrl(), $urlContext->getSubjectObject());
            }

            $urlContext->setRoute($route);
        }

        $this->pendingUrlContextStacks[] = $urlContextStack;
    }

    public function handleDefunctRoutes()
    {
        while ($urlContextStack = array_pop($this->pendingUrlContextStacks)) {
            $this->defunctRouteHandler->handleDefunctRoutes($urlContextStack);
        }
    }

    private function getUrlContextsForDocument(UrlContextStack $urlContextStack)
    {
        $locales = $this->adapter->getLocales($urlContextStack->getSubjectObject()) ? : array(null);

        foreach ($locales as $locale) {
            if (null !== $locale) {
                $this->adapter->translateObject($urlContextStack->getSubjectObject(), $locale);
            }

            // create and add url context to stack
            $urlContext = $urlContextStack->createUrlContext($locale);

            // generate the URL
            $url = $this->urlGenerator->generateUrl($urlContext);

            // update the context with the URL
            $urlContext->setUrl($url);
        }
    }
}
