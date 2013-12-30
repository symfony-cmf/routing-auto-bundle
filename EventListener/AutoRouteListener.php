<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\EventListener;

use Doctrine\Common\Persistence\Event\ManagerEventArgs;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute;

/**
 * Doctrine PHPCR ODM listener for maintaining automatic routes.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteListener
{
    protected $inFlush = false;

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
        if (true === $this->inFlush) {
            return;
        }

        /** @var $dm DocumentManager */
        $dm = $args->getObjectManager();
        $uow = $dm->getUnitOfWork();

        $scheduledInserts = $uow->getScheduledInserts();
        $scheduledUpdates = $uow->getScheduledUpdates();
        $updates = array_merge($scheduledInserts, $scheduledUpdates);

        foreach ($updates as $document) {
            if ($this->getArm()->isAutoRouteable($document)) {
                $contexts = $this->getArm()->updateAutoRouteForDocument($document);

                $persistedRoutes = array();

                foreach ($contexts as $context) {
                    foreach ($context->getRoutes() as $route) {

                        if ($route instanceof AutoRoute) {
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

                        // this was originally computeSingleDocumentChangeset
                        // however this caused problems in a real usecase
                        // (functional tests were fine)
                        //
                        // this is probably not very efficient, but it works
                        $uow->computeChangeSets();
                    }
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

        foreach ($updates as $document) {
            if ($this->getArm()->isAutoRouteable($document)) {
                $contexts = $this->getArm()->updateAutoRouteForDocument($document);

                // todo: This is a hack to workaround the fact that PHPCR-ODM does not 
                //       take into acount the order of operations, which causes documents to
                //       exist where they shouldn't do during the commiting process.
                $this->inFlush = true;
                $dm->flush();
                $this->inFlush = false;

                foreach ($contexts as $context) {
                    $extraDocuments = $context->getExtraDocuments();

                    foreach ($extraDocuments as $extraDocument) {
                        $dm->persist($extraDocument);
                    }
                }
            }
        }
    }
}
