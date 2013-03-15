<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\PHPCR\Event;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;
use Doctrine\ODM\PHPCR\Event\PostFlushEventArgs;
use Doctrine\ODM\PHPCR\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Doctrine PHPCR ODM Subscriber for maintaining automatic routes.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteSubscriber implements EventSubscriber
{
    protected $persistQueue = array();
    protected $removeQueue = array();

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            Event::onFlush,
        );
    }

    protected function getArm()
    {
        return $this->container->get('symfony_cmf_routing_auto_route.auto_route_manager');
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        $scheduledInserts = $uow->getScheduledInserts();
        $scheduledUpdates = $uow->getScheduledUpdates();
        $updates = array_merge($scheduledInserts, $scheduledUpdates);

        foreach ($updates as $document) {
            if ($this->getArm()->isAutoRouteable($document)) {
                $context = $this->getArm()->updateAutoRouteForDocument($document);
                foreach ($context->getRouteStack() as $route) {
                    $dm->persist($route);
                    $uow->computeSingleDocumentChangeSet($route);
                }
            }
        }

        $removes = $uow->getScheduledRemovals();

        foreach ($removes as $document) {
            if ($this->getArm()->isAutoRouteable($document)) {
                $routes = $this->getArm()->fetchAutoRoutesForDocument($document);
                foreach ($routes as $route) {
                    $uow->scheduleRemove($route);
                }
            }
        }
    }
}

