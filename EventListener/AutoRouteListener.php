<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\EventListener;

use Doctrine\Common\Persistence\Event\ManagerEventArgs;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;

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

        foreach ($updates as $document) {
            if ($this->getArm()->isAutoRouteable($document)) {
                $context = $this->getArm()->updateAutoRouteForDocument($document);
                foreach ($context->getRoutes() as $route) {
                    $dm->persist($route);

                    // this was originally computeSingleDocumentChangeset
                    // however this caused problems in a real usecase
                    // (functional tests were fine)
                    //
                    // this is probably not very efficient, but it works
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
