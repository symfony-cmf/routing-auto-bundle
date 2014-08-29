<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Adapter;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Document\Generic;
use Doctrine\Common\Util\ClassUtils;
use PHPCR\InvalidItemStateException;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRedirectRoute;

/**
 * Adapter for PHPCR-ODM
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PhpcrOdmAdapter implements AdapterInterface
{
    const TAG_NO_MULTILANG = 'no-multilang';

    protected $dm;
    protected $baseRoutePath;
    protected $autoRouteFqcn;

    /**
     * @param DocumentManager $dm
     * @param string          $routeBasePath Route path for all routes
     * @param string          $autoRouteFqcn The FQCN of the AutoRoute document to use
     */
    public function __construct(DocumentManager $dm, $routeBasePath, $autoRouteFqcn = 'Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute')
    {
        $this->dm = $dm;
        $this->baseRoutePath = $routeBasePath;

        $reflection = new \ReflectionClass($autoRouteFqcn);
        if (!$reflection->isSubclassOf('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface')) {
            throw new \InvalidArgumentException(sprintf('AutoRoute documents have to implement the AutoRouteInterface, "%s" does not.', $autoRouteFqcn));
        }

        $this->autoRouteFqcn = $autoRouteFqcn;
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
    public function generateAutoRouteTag(UriContext $uriContext)
    {
        return $uriContext->getLocale() ? : self::TAG_NO_MULTILANG;
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
    public function createAutoRoute($uri, $contentDocument, $autoRouteTag)
    {
        $path = $this->baseRoutePath;
        $document = $parentDocument = $this->dm->find(null, $path);
        if (null === $parentDocument) {
            throw new \RuntimeException(sprintf('The "route_basepath" configuration points to a non-existant path "%s".',
                $path
            ));
        }

        $segments = preg_split('#/#', $uri, null, PREG_SPLIT_NO_EMPTY);
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

        $headRoute = new $this->autoRouteFqcn();
        $headRoute->setContent($contentDocument);
        $headRoute->setName($headName);
        $headRoute->setParent($document);
        $headRoute->setAutoRouteTag($autoRouteTag);
        $headRoute->setType(AutoRouteInterface::TYPE_PRIMARY);

        return $headRoute;
    }

    /**
     * {@inheritDoc}
     */
    public function createRedirectRoute(AutoRouteInterface $referringAutoRoute, AutoRouteInterface $newRoute)
    {
        $referringAutoRoute->setRedirectTarget($newRoute);
        $referringAutoRoute->setType(AutoRouteInterface::TYPE_REDIRECT);
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
    public function compareAutoRouteContent(AutoRouteInterface $autoRoute, $contentDocument)
    {
        if ($autoRoute->getContent() === $contentDocument) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getReferringAutoRoutes($contentDocument)
    {
         return $this->dm->getReferrers($contentDocument, null, null, null, 'Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
    }

    /**
     * {@inheritDoc}
     */
    public function findRouteForUri($uri)
    {
        $path = $this->getPathFromUri($uri);

        return $this->dm->find(null, $path);
    }

    private function getPathFromUri($uri)
    {
        return $this->baseRoutePath . $uri;
    }
}
