<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Driver;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Document\Generic;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Abstraction driver for PHPCR-ODM
 *
 * This class will eventually encapsulate all of the PHPCR-ODM
 * specific logic to enable support for multiple backends.
 */
class PhpcrOdmDriver implements DriverInterface
{
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
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
        $this->dm->findTranslation(get_class($contentDocument), $meta->getIdentifierValue($contentDocument), $locale);

        return $contentDocument;
    }

    public function createRoute($path, $contentDocument)
    {
        $pathElements = explode('/', $path);
        $head = array_pop($parts);
        $parentPath = implode('/', $pathElements);

        // bypass the ODM ... but changes will still only be
        // persisted when the PHPCR session is saved in the ODMs flush().
        PathHelper::createPath($parentPath);

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
        return ClassUtils::getRealClassName($className);
    }

    public function compareRouteContent(RouteObjectInterface $route, $document)
    {
        if ($route->getContent() === $document) {
            return true;
        }

        return false;
    }

    public function getReferringRoutes($document)
    {
         return $this->dm->getReferrers($document, null, null, null, 'Symfony\Cmf\Component\Routing\RouteObjectInterface');
    }
}
