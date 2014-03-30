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

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Document\Generic;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use PHPCR\Util\NodeHelper;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute;
use PHPCR\InvalidItemStateException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRouteInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext;

/**
 * Adapter for PHPCR-ODM
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PhpcrOdmAdapter implements AdapterInterface
{
    protected $dm;
    protected $baseRoutePath;

    /**
     * @param DocumentManager $dm
     * @param string $routeBasePath Route path for all routes
     */
    public function __construct(DocumentManager $dm, $routeBasePath)
    {
        $this->dm = $dm;
        $this->baseRoutePath = $routeBasePath;
    }

    /**
     * {@inheritDoc}
     */
    public function getLocales($contentDocument)
    {
        if ($this->dm->isDocumentTranslatable($contentDocument)) {
            return $this->dm->getLocalesFor($contentDocument);
        }

        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function translateObject($contentDocument, $locale)
    {
        $meta = $this->dm->getMetadataFactory()->getMetadataFor(get_class($contentDocument));
        $contentDocument = $this->dm->findTranslation($meta->getName(), $meta->getIdentifierValue($contentDocument), $locale);

        return $contentDocument;
    }

    /**
     * {@inheritDoc}
     */
    public function generateAutoRouteTag(UrlContext $urlContext)
    {
        return $urlContext->getLocale();
    }

    /**
     * {@inheritDoc}
     */
    public function removeDefunctRoute($route, $newRoute)
    {
        $session = $this->dm->getPhpcrSession();
        try {
            $node = $this->dm->getNodeForDocument($route);
            $newNode = $this->dm->getNodeForDocument($newRoute);
        } catch (InvalidItemStateException $e) {
            // nothing ..
        }

        $session->save();
    }

    /**
     * {@inheritDoc}
     */
    public function migrateAutoRouteChildren(AutoRouteInterface $srcAutoRoute, AutoRouteInterface $destAutoRoute)
    {
        $session = $this->dm->getPhpcrSession();
        $srcAutoRouteNode = $this->dm->getNodeForDocument($srcAutoRoute);
        $destAutoRouteNode = $this->dm->getNodeForDocument($destAutoRoute);

        $srcAutoRouteChildren = $srcAutoRouteNode->getNodes();

        foreach ($srcAutoRouteChildren as $srcAutoRouteChild) {
            $session->move($srcAutoRouteChild->getPath(), $destAutoRouteNode->getPath() . '/' . $srcAutoRouteChild->getName());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeAutoRoute(AutoRouteInterface $autoRoute)
    {
        $session = $this->dm->getPhpcrSession();
        $node = $this->dm->getNodeForDocument($autoRoute);
        $session->removeItem($node->getPath());
        $session->save();
    }

    /**
     * {@inheritDoc}
     */
    public function createRoute($url, $contentDocument, $autoRouteTag)
    {
        $path = $this->baseRoutePath;
        $parentDocument = $this->dm->find(null, $path);

        $segments = preg_split('#/#', $url, null, PREG_SPLIT_NO_EMPTY);
        $headName = array_pop($segments);
        foreach ($segments as $segment) {
            $path .= '/' . $segment;
            $document = $this->dm->find(null, $path);

            if (null === $document) {
                $document = new Generic();
                $document->setParent($parentDocument);
                $document->setNodeName($segment);
                $this->dm->persist($document);
            }
            $parentDocument = $document;
        }

        $headRoute = new AutoRoute();
        $headRoute->setContent($contentDocument);
        $headRoute->setName($headName);
        $headRoute->setParent($document);
        $headRoute->setAutoRouteTag($autoRouteTag);

        return $headRoute;
    }

    /**
     * {@inheritDoc}
     */
    public function getRealClassName($className)
    {
        return ClassUtils::getRealClass($className);
    }

    /**
     * {@inheritDoc}
     */
    public function compareRouteContent(RouteObjectInterface $route, $contentDocument)
    {
        if ($route->getContent() === $contentDocument) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getReferringRoutes($contentDocument)
    {
         return $this->dm->getReferrers($contentDocument, null, null, null, 'Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRouteInterface');
    }

    /**
     * {@inheritDoc}
     */
    public function findRouteForUrl($url)
    {
        $path = $this->getPathFromUrl($url);
        return $this->dm->find(null, $path);
    }

    private function getPathFromUrl($url)
    {
        return $this->baseRoutePath . $url;
    }
}
