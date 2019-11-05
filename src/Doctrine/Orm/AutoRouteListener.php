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
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Adapter\OrmAdapter;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;
use Symfony\Cmf\Component\RoutingAuto\Mapping\Exception\ClassNotMappedException;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WAM\Bundle\CoreBundle\Model\TranslationInterface;
use WAM\Bundle\RoutingBundle\Entity\AutoRoute;
use WAM\Bundle\RoutingBundle\Entity\Url;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Doctrine ORM listener for maintaining automatic routes.
 *
 * @author Noel Garcia <ngarcia@wearemarketing.com>
 */
class AutoRouteListener
{
    use ContainerAwareTrait;

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

    /**
     * Removes from the updates array the entities that aren't autoroutable
     * Also, just in case of translations, puts into updates array the translatable entity if it wasn't yet
     *
     * @param array $inEntities
     * @return array
     */
    private function parseUpdateEntities(array $inEntities)
    {
        $outEntities = array();
        foreach ($inEntities as $entity) {
            if ($this->isAutoRouteable($entity)) {
                $outEntities[] = $entity;
            } else if ($entity instanceof TranslationInterface && $translatable = $entity->getTranslatable()) {
                if (false === in_array($translatable, $outEntities, true) && false === in_array($translatable, $inEntities, true) && $this->isAutoRouteable($translatable)) {
                    $outEntities[] = $translatable;
                }
            }
        }

        return $outEntities;
    }

    /**
     * Checks autoroutable entities based on configuration
     *
     * @param $document
     * @return bool
     */
    private function isAutoRouteable($document)
    {
        try {
            $metadataFactory = $this->getMetadataFactory();
            $classMetadata = $metadataFactory->getMetadataForClass(get_class($document));
            return (boolean)$classMetadata;
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

        $scheduledInsertions = $unitOfWork->getScheduledEntityInsertions();
        $scheduledUpdates = $unitOfWork->getScheduledEntityUpdates();
        $updates = array_merge($scheduledInsertions, $scheduledUpdates);
        $parsedUpdates = $this->parseUpdateEntities($updates);

        $arm = $this->getAutoRouteManager();

        //create routes for insertions and updates
        $autoRoute = null;
        foreach ($parsedUpdates as $entity) {
            $new = in_array($entity, $scheduledInsertions, true);
            $uriContextCollection = new UriContextCollection($entity);
            $arm->buildUriContextCollection($uriContextCollection);

            foreach ($uriContextCollection->getUriContexts() as $uriContext) {
                $autoRoute = $uriContext->getAutoRoute();
                if ($entity instanceof RouteReferrersInterface) {
                    $entity->addRoute($autoRoute);
                }

                $manager->persist($autoRoute);

                //set persistence order to allow set in the route the contentEntity PK
                if ($new) {

                    $fromHash = $this->getEntityMetadata($manager, $entity)->name;

                    $commitOrderCalculator->addNode($fromHash, $entity);

                    $commitOrderCalculator->addDependency(
                        $fromHash,
                        $this->getEntityMetadata($manager, $autoRoute)->name,
                        1
                    );
                }

                $unitOfWork->computeChangeSet($this->getEntityMetadata($manager, $autoRoute), $autoRoute);
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
                    $id = $this->getEntityMetadata($manager, $entity)->getIdentifierValues($entity);
                    $autoRoute->setContentId($id);
                    $this->replaceIdOnNameField($autoRoute, $id, 'name');
                    $this->replaceIdOnNameField($autoRoute, $id, 'canonicalName');

                    $unitOfWork->computeChangeSet($this->getEntityMetadata($manager, $autoRoute), $autoRoute);
                }
            }
        }
    }

    /**
     * Tries to replace in route name or canonicalName the id placeholder by the real row id
     *
     * @param AutoRouteInterface $autoRoute
     * @param array $id
     * @param string $nameField
     */
    private function replaceIdOnNameField(AutoRouteInterface $autoRoute, array $id, $nameField)
    {
        $fieldName = 'name' == $nameField ? 'Name' : 'CanonicalName';
        $getter = "get$fieldName";
        $setter = "set$fieldName";

        $autoRoute->$setter(
            str_replace(
                OrmAdapter::ID_PLACEHOLDER,
                implode('_', $id),
                $autoRoute->$getter()
            )
        );
    }
}
