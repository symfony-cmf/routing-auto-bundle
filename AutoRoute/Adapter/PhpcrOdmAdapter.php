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

/**
 * Abstraction adapter for PHPCR-ODM
 *
 * This class will eventually encapsulate all of the PHPCR-ODM
 * specific logic to enable support for multiple backends.
 */
class PhpcrOdmAdapter implements AdapterInterface
{
    protected $dm;
    protected $baseRoutePath;

    public function __construct(DocumentManager $dm, $routeBasePath)
    {
        $this->dm = $dm;
        $this->baseRoutePath = $routeBasePath;
    }

    public function getLocales($contentDocument)
    {
        if ($this->dm->isDocumentTranslatable($contentDocument)) {
            return $this->dm->getLocalesFor($contentDocument);
        }

        return array();
    }

    public function translateObject($contentDocument, $locale)
    {
        $meta = $this->dm->getMetadataFactory()->getMetadataFor(get_class($contentDocument));
        $contentDocument = $this->dm->findTranslation($meta->getName(), $meta->getIdentifierValue($contentDocument), $locale);

        return $contentDocument;
    }

    public function removeDefunctRoute($route, $newRoute)
    {
        $session = $this->dm->getPhpcrSession();
        try {
            $node = $this->dm->getNodeForDocument($route);
            $newNode = $this->dm->getNodeForDocument($newRoute);
            $nodeChildren = $node->getNodes();

            foreach ($nodeChildren as $nodeChild) {
                $session->move($nodeChild->getPath(), $newNode->getPath() . '/' . $nodeChild->getName());
            }
            $session->removeItem($node->getPath());
        } catch (InvalidItemStateException $e) {
            // nothing ..
        }

        $session->save();
    }

    public function createRoute($url, $contentDocument)
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

        return $headRoute;
    }

    public function getRealClassName($className)
    {
        return ClassUtils::getRealClass($className);
    }

    public function compareRouteContent(RouteObjectInterface $route, $contentDocument)
    {
        if ($route->getContent() === $contentDocument) {
            return true;
        }

        return false;
    }

    public function getReferringRoutes($contentDocument)
    {
         return $this->dm->getReferrers($contentDocument, null, null, null, 'Symfony\Cmf\Component\Routing\RouteObjectInterface');
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

