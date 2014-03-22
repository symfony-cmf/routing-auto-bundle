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
use Doctrine\Common\Util\ClassUtils;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\OperationStack;

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

                $operationStack = new OperationStack($document);
                $arm->buildOperationStack($operationStack);

                // refactor this.
                foreach ($operationStack->getUrlContexts() as $urlContext) {
                    $newRoute = $urlContext->getNewRoute();
                    if (null === $newRoute) {
                        continue;
                    }
                    $dm->persist($newRoute);
                    $uow->computeChangeSets();
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
                foreach ($referrers as $route) {
                    $uow->scheduleRemove($route);
                }
            }
        }
    }

    public function postFlush()
    {
        $arm = $this->getAutoRouteManager();
        $arm->handleDefunctRoutes();
    }

    private function isAutoRouteable($document)
    {
        return $this->getMetadataFactory()->getMetadataForClass(get_class($document));
    }
}
