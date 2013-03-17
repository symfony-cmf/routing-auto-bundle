<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteMaker
{
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function createOrUpdateAutoRoute(AutoRouteStack $autoRouteStack)
    {
        $content = $autoRouteStack->getContext()->getContent();

        $autoRoute = $this->getAutoRouteForDocument($content);

        if (null === $autoRoute) {
            $autoRoute = new AutoRoute;
            $autoRoute->setRouteContent($content);
        }

        $autoRouteStack->addRoute($autoRoute);
    }

    protected function getAutoRouteForDocument($document)
    {
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

        return $referrers->first();
    }
}
