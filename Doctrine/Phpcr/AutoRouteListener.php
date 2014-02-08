<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Doctrine\Phpcr;

use Doctrine\Common\Persistence\Event\ManagerEventArgs;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute;

/**
 * Doctrine PHPCR ODM listener for maintaining automatic routes.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteListener
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return AutoRouteManager
     */
    protected function getArm()
    {
        // lazy load the auto_route_manager service to prevent a cirular-reference
        // to the document manager.
        return $this->container->get('cmf_routing_auto.auto_route_manager');
    }

    public function onFlush(ManagerEventArgs $args)
    {
        /** @var $dm DocumentManager */
        $dm = $args->getObjectManager();
        $uow = $dm->getUnitOfWork();

        $scheduledInserts = $uow->getScheduledInserts();
        $scheduledUpdates = $uow->getScheduledUpdates();
        $updates = array_merge($scheduledInserts, $scheduledUpdates);

        $autoRoute = null;
        foreach ($updates as $document) {
            if ($this->getArm()->isAutoRouteable($document)) {
                $contexts = $this->getArm()->updateAutoRouteForDocument($document);

                $persistedRoutes = array();

                foreach ($contexts as $context) {
                    foreach ($context->getRoutes() as $route) {

                        if ($route instanceof AutoRoute) {
                            $autoRoute = $route;
                            $routeParent = $route->getParent();
                            $id = spl_object_hash($routeParent).$route->getName();
                        } else {
                            $metadata = $dm->getClassMetadata(get_class($route));
                            $id = $metadata->getIdentifierValue($route);
                        }

                        if (isset($persistedRoutes[$id])) {
                            continue;
                        }

                        $dm->persist($route);
                        $persistedRoutes[$id] = true;
                    }

                    $uow->computeChangeSets();

                    // For some reason the AutoRoute is not updated even though
                    // it is persisted above. Re-persisting and recomputing the
                    // changesets makes this work.
                    if (null !== $autoRoute) {
                        $dm->persist($autoRoute);
                    }

                    $uow->computeChangeSets();
                }
            }
        }

        $removes = $uow->getScheduledRemovals();

        foreach ($removes as $document) {
            if ($this->getArm()->isAutoRouteable($document)) {
                $referrers = $dm->getReferrers($document);
                $referrers = $referrers->filter(function ($referrer) {
                    if ($referrer instanceof AutoRoute) {
                        return true;
                    }

                    return false;
                });
                foreach ($referrers as $route) {
                    $uow->scheduleRemove($route);
                }
            }
        }
    }
}
