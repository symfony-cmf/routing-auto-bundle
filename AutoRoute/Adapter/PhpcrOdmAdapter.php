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

    public function createRoute($path, $contentDocument)
    {
        $pathElements = explode('/', $path);
        $headName = array_pop($pathElements);
        $parentPath = implode('/', $pathElements);

        // bypass the ODM ... but changes will still only be
        // persisted when the PHPCR session is saved in the ODMs flush().
        NodeHelper::createPath($this->dm->getPhpcrSession(), $parentPath);

        $autoRouteParent = $this->dm->find(null, $parentPath);

        if (!$autoRouteParent) {
            throw new \RuntimeException(sprintf(
                'Hmph, could not find parent path "%s", this really should not have happened.',
                $parentPath
            ));
        }

        $headRoute = new AutoRoute();
        $headRoute->setContent($contentDocument);
        $headRoute->setName($headName);
        $headRoute->setParent($autoRouteParent);

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
        return $this->dm->find(null, $this->baseRoutePath . $url);
    }
}
