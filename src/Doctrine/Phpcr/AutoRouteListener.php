<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Doctrine\Phpcr;

use Doctrine\Common\Persistence\Event\ManagerEventArgs;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;
use Symfony\Cmf\Component\RoutingAuto\Mapping\Exception\ClassNotMappedException;

/**
 * Doctrine PHPCR ODM listener for maintaining automatic routes.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteListener
{
    protected $postFlushDone = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return AutoRouteManager
     */
    protected function getAutoRouteManager()
    {
        // lazy load the auto_route_manager service to prevent a cirular-reference
        // to the document manager.
        return $this->container->get('cmf_routing_auto.auto_route_manager');
    }

    protected function getMetadataFactory()
    {
        return $this->container->get('cmf_routing_auto.metadata.factory');
    }

    public function onFlush(ManagerEventArgs $args)
    {
        /** @var $dm DocumentManager */
        $dm = $args->getObjectManager();
        $uow = $dm->getUnitOfWork();
        $arm = $this->getAutoRouteManager();

        $scheduledInserts = $uow->getScheduledInserts();
        $scheduledUpdates = $uow->getScheduledUpdates();
        $updates = array_merge($scheduledInserts, $scheduledUpdates);

        $autoRoute = null;
        foreach ($updates as $document) {
            if ($this->isAutoRouteable($document)) {
                $locale = $uow->getCurrentLocale($document);

                $uriContextCollection = new UriContextCollection($document);
                $arm->buildUriContextCollection($uriContextCollection);

                // refactor this.
                foreach ($uriContextCollection->getUriContexts() as $uriContext) {
                    $autoRoute = $uriContext->getAutoRoute();
                    $dm->persist($autoRoute);
                    $uow->computeChangeSets();
                }

                // reset locale to the original locale
                if (null !== $locale) {
                    $dm->findTranslation(get_class($document), $uow->getDocumentId($document), $locale);
                }
            }
        }

        $removes = $uow->getScheduledRemovals();
        foreach ($removes as $document) {
            if ($this->isAutoRouteable($document)) {
                $referrers = $dm->getReferrers($document);
                $referrers = $referrers->filter(function ($referrer) {
                    if ($referrer instanceof AutoRoute) {
                        return true;
                    }

                    return false;
                });
                foreach ($referrers as $autoRoute) {
                    $uow->scheduleRemove($autoRoute);
                }
            }
        }
    }

    public function endFlush(ManagerEventArgs $args)
    {
        $dm = $args->getObjectManager();
        $arm = $this->getAutoRouteManager();
        $arm->handleDefunctRoutes();

        if (!$this->postFlushDone) {
            $this->postFlushDone = true;
            $dm->flush();
        }

        $this->postFlushDone = false;
    }

    private function isAutoRouteable($document)
    {
        try {
            return (bool) $this->getMetadataFactory()->getMetadataForClass(get_class($document));
        } catch (ClassNotMappedException $e) {
            return false;
        }
    }
}
