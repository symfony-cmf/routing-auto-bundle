<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Doctrine\Orm;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WAM\Bundle\RoutingBundle\Entity\AutoRoute;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;
use Symfony\Cmf\Component\RoutingAuto\Mapping\Exception\ClassNotMappedException;

/**
 * Doctrine ORM ODM listener for maintaining automatic routes.
 *
 * @author Noel Garcia <ngarcia@wearemarketing.com>
 */
class AutoRouteListener extends ContainerAware
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return AutoRouteManager
     */
    private function getAutoRouteManager()
    {
        // lazy load the auto_route_manager service to prevent a cirular-reference
        // to the document manager.
        return $this->container->get('cmf_routing_auto.auto_route_manager');
    }

    private function getMetadataFactory()
    {
        return $this->container->get('cmf_routing_auto.metadata.factory');
    }

    private function isAutoRouteable($document)
    {
        try {
            return (boolean)$this->getMetadataFactory()->getMetadataForClass(get_class($document));
        } catch (ClassNotMappedException $e) {
            return false;
        }
    }

    /**
     * @param EntityManager $manager
     * @param $entityObject
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    private function getEntityMetadata(EntityManager $manager, $entityObject)
    {
        return $manager->getClassMetadata(get_class($entityObject));
    }

    /**
     * Here is almost all the magic, new routes are guessed, created and persisted
     *
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $manager = $eventArgs->getEntityManager();
        $unitOfWork = $manager->getUnitOfWork();
        $commitOrderCalculator = $unitOfWork->getCommitOrderCalculator();

        $scheduledEntityInsertions = $unitOfWork->getScheduledEntityInsertions();
        $scheduledCollectionUpdates = $unitOfWork->getScheduledEntityUpdates();
        $updates = array_merge($scheduledEntityInsertions, $scheduledCollectionUpdates);

        $arm = $this->getAutoRouteManager();

        //create routes for insertions and updates
        $autoRoute = null;
        foreach ($updates as $entity) {
            if ($this->isAutoRouteable($entity)) {

                $uriContextCollection = new UriContextCollection($entity);
                $arm->buildUriContextCollection($uriContextCollection);

                // refactor this.
                foreach ($uriContextCollection->getUriContexts() as $uriContext) {
                    $autoRoute = $uriContext->getAutoRoute();
                    if ($entity instanceof RouteReferrersInterface) {
                        $entity->addRoute($autoRoute);
                    }

                    $manager->persist($autoRoute);

                    //modify persistence order in order to persist the route after the entity to get the entity PK
                    $commitOrderCalculator->addDependency(
                        $this->getEntityMetadata($manager, $entity),
                        $this->getEntityMetadata($manager, $autoRoute)
                    );
                    $unitOfWork->computeChangeSet($this->getEntityMetadata($manager, $autoRoute), $autoRoute);
                }
            }
        }

        //for all entity removals, remove the routes too
        $removes = $unitOfWork->getScheduledEntityDeletions();
        foreach ($removes as $entity) {
            if ($this->isAutoRouteable($entity)) {
                foreach ($entity->getRoutes() as $route) {
                    $manager->remove($route);
                }
            }
        }
    }

    /**
     * Here is where the decision about old routes are taken. See defunc handlers.
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->getAutoRouteManager()->handleDefunctRoutes();
    }

    /**
     * After persist the entity, its id can (and shall) be retrieved and setted on the route
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($this->isAutoRouteable($entity)) {
            $manager = $eventArgs->getEntityManager();
            $unitOfWork = $manager->getUnitOfWork();
            /** @var AutoRoute $autoRoute */
            foreach ($entity->getRoutes() as $autoRoute) {
                if (!$autoRoute->getContentId()) {
                    $autoRoute->setContentId($this->getEntityMetadata($manager, $entity)->getIdentifierValues($entity));
                    $unitOfWork->recomputeSingleEntityChangeSet($this->getEntityMetadata($manager, $autoRoute), $autoRoute);
                }
            }
        }
    }
}
