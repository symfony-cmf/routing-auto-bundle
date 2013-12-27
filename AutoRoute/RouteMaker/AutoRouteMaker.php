<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker;

use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMakerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

/**
 * This class is responsible for creating and updating the actual
 * AutoRoute documents.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteMaker implements RouteMakerInterface
{
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function make(RouteStack $routeStack)
    {
        $context = $routeStack->getContext();
        $content = $context->getContent();

        $autoRoute = $this->getAutoRouteForDocument($content);

        if (!$autoRoute) {
            $autoRoute = new AutoRoute;
            $autoRoute->setParent($context->getTopRoute());
            $autoRoute->setContent($content);
        }

        $autoRoute->setName($routeStack->getPath());

        $routeStack->addRoute($autoRoute);
    }

    protected function getAutoRouteForDocument($document)
    {
        if (!$this->documentIsPersisted($document)) {
            return null;
        }

        $dm = $this->dm;
        $uow = $dm->getUnitOfWork();

        $referrers = $this->dm->getReferrers($document);

        if ($referrers->count() == 0) {
            return null;
        }

        // Filter non auto-routes
        $referrers = $referrers->filter(function ($referrer) {
            if ($referrer instanceof AutoRoute) {
                return true;
            }

            return false;
        });

        $metadata = $dm->getClassMetadata(get_class($document));

        $locale = null; // $uow->getLocale($document, $locale);

        // If the document is translated, filter locales
        if (null !== $locale) {
            throw new \Exception(
                'Translations not yet supported for Auto Routes - '.
                'Should be easy.'
            );

            // array_filter($referrers, function ($referrer) use ($dm, $uow, $locale) {
            //     $metadata = $dm->getClassMetadata($refferer);
            //     if ($locale == $uow->getLocaleFor($referrer, $referrer)) {
            //         return true;
            //     }

            //     return false;
            // });
        }

        if ($referrers->count() > 1) {
            throw new \RuntimeException(sprintf(
                'More than one auto route for document "%s"',
                get_class($document)
            ));
        }

        return $referrers->current();
    }

    protected function documentIsPersisted($document)
    {
        $id = $this->dm->getUnitOfWork()->getDocumentId($document);
        $phpcrSession = $this->dm->getPhpcrSession();
        return $phpcrSession->nodeExists($id);
    }
}
