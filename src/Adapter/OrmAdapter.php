<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Adapter;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Doctrine\Orm\AutoRoute;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;

/**
 * Adapter for ORM.
 *
 * @author WAM Team <develop@wearemarketing.com>
 */
class OrmAdapter implements AdapterInterface, AutoRouteRefreshCommandAdapterInterface
{
    const TAG_NO_MULTILANG = 'no-multilang';

    const ID_PLACEHOLDER = '%ID_PLACEHOLDER%';

    const REQUEST_LOCALE_ATTRIBUTE = '_locale';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $autoRouteFqcn;

    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * @param EntityManagerInterface $em
     * @param string                 $autoRouteFqcn The FQCN of the AutoRoute document to use
     */
    public function __construct(EntityManagerInterface $em, $autoRouteFqcn)
    {
        $this->em = $em;

        $reflection = new \ReflectionClass($autoRouteFqcn);
        if (!$reflection->isSubclassOf(AutoRouteInterface::class)) {
            throw new \InvalidArgumentException(sprintf('AutoRoute documents have to implement the AutoRouteInterface, "%s" does not.', $autoRouteFqcn));
        }

        $this->autoRouteFqcn = $autoRouteFqcn;

        $this->repository = $this->em->getRepository($this->autoRouteFqcn);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales($contentDocument)
    {
        // TODO: look for better approach. This is because we are using knp doctrine behaviour lib
        if ($contentDocument instanceof TranslatableInterface) {
            return array_keys($contentDocument->getTranslations()->toArray());
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function translateObject($contentDocument, $locale)
    {
        $contentDocument->setCurrentLocale($locale);

        return $contentDocument->translate(null, false);
    }

    /**
     * {@inheritdoc}
     */
    public function generateAutoRouteTag(UriContext $uriContext)
    {
        return $uriContext->getLocale() ?: self::TAG_NO_MULTILANG;
    }

    /**
     * {@inheritdoc}
     */
    public function migrateAutoRouteChildren(AutoRouteInterface $srcAutoRoute = null, AutoRouteInterface $destAutoRoute = null)
    {
        // It is not implemented tree relationship between routes
        // TODO: if we implemented tree we can use this method for propageting update.
        // Indeed, we can try to use this method to update route when depends on other one like /category/product and we change category we have to apdate all products.
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAutoRoute(AutoRouteInterface $autoRoute)
    {
        $this->em->remove($autoRoute);
        $this->em->flush($autoRoute);
    }

    /**
     * {@inheritdoc}
     */
    public function createAutoRoute(UriContext $uriContext, $autoRouteTag)
    {
        $contentDocument = $uriContext->getSubject();
        $documentClassName = get_class($contentDocument);
        /** @var ClassMetadata $metadata */
        $metadata = $this->em->getClassMetadata($documentClassName);
        $id = $metadata->getIdentifierValues($contentDocument);
        $defaults = $uriContext->getDefaults();

        /** @var AutoRoute $headRoute */
        $headRoute = new $this->autoRouteFqcn();
        $headRoute->setDefaults($defaults);
        $headRoute->setContent($contentDocument);
        $headRoute->setStaticPrefix($uriContext->getUri());
        $headRoute->setLocale($autoRouteTag);
        $headRoute->setType(AutoRouteInterface::TYPE_PRIMARY);
        $headRoute->setContentClass($documentClassName);
        $headRoute->setContentId($id);

        //Route name is compound by: table name, row id, locale if present, type, unique id
        $routeNameParts = array_merge(
            [$metadata->getTableName()],
            $id ? array_values($id) : [self::ID_PLACEHOLDER]
        );

        if (!empty($defaults['type'])) {
            $routeNameParts[] = $defaults['type'];
            $headRoute->setRequirement('type', $defaults['type']);
        }

        $headRoute->setCanonicalName(implode('_', $routeNameParts));
        if (self::TAG_NO_MULTILANG !== $autoRouteTag) {
            $headRoute->setRequirement(self::REQUEST_LOCALE_ATTRIBUTE, $autoRouteTag);
            $headRoute->setDefault(self::REQUEST_LOCALE_ATTRIBUTE, $autoRouteTag);
            $routeNameParts[] = $autoRouteTag;
        }

        $routeNameParts[] = uniqid();
        $headRoute->setName(implode('_', $routeNameParts));

        return $headRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function createRedirectRoute(AutoRouteInterface $referringAutoRoute, AutoRouteInterface $newRoute)
    {
        // check if $newRoute already exists
        $route = $this->repository->findOneByStaticPrefix($newRoute->getStaticPrefix());

        if ($route) {
            // in case it's a redirection, remove redirection's defaults
            $defaults = $route->getDefaults();
            unset($defaults['_controller'], $defaults['route'], $defaults['permanent']);
            $route->setDefaults($defaults);
            $this->em->flush($route);
        }

        $referringAutoRoute->setRedirectTarget($newRoute);
        $referringAutoRoute->setPosition($this->calculateReferringRoutePosition($newRoute->getPosition()));
        $referringAutoRoute->setType(AutoRouteInterface::TYPE_REDIRECT);

        // WARNING http://doctrine-orm.readthedocs.org/en/latest/reference/events.html#postflush
        // Acording with doctrine doc you can not call em:flush into postFlush event
        // but it seems it works fine when you call it with the entity that you want to persist
        $this->em->flush($referringAutoRoute);
    }

    /**
     * {@inheritdoc}
     */
    public function getRealClassName($className)
    {
        return ClassUtils::getRealClass($className);
    }

    /**
     * {@inheritdoc}
     */
    public function compareAutoRouteContent(AutoRouteInterface $autoRoute, $contentDocument)
    {
        if ($autoRoute->getContent() === $contentDocument) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferringAutoRoutes($contentDocument)
    {
        return $contentDocument->getRoutes();
    }

    /**
     * {@inheritdoc}
     */
    public function findRouteForUri($uri, UriContext $uriContext)
    {
        $route = $this->repository->findOneBy(['staticPrefix' => $uri]);
        if (empty($route)) {
            return null;
        }

        $this->resolveRouteContent($route);

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function compareAutoRouteLocale(AutoRouteInterface $autoRoute, $locale)
    {
        $autoRouteLocale = $autoRoute->getLocale();
        if (self::TAG_NO_MULTILANG === $autoRouteLocale) {
            $autoRouteLocale = null;
        }

        return $autoRouteLocale === $locale;
    }

    public function getRoutes($entity)
    {
        $className = $this->getClassName($entity);
        $id = $this->em->getClassMetadata($className)->getIdentifierValues($entity);

        //this workaround is needed to bypass doctrine escaping parameters
        $dql = sprintf(
            "select o from %s o WHERE o.%s = '%s' and o.%s = '%s' order by o.position",
            $this->autoRouteFqcn,
            AutoRoute::CONTENT_CLASS_KEY,
            $className,
            AutoRoute::CONTENT_ID_KEY,
            json_encode($id)
        );

        $routes = $this->em->createQuery($dql)->getResult();

        return $routes;
    }

    public function resolveRouteContent(AutoRoute $autoRoute)
    {
        $class = $autoRoute->getContentClass();
        $id = $autoRoute->getContentId();
        if (empty($class) or empty($id)) {
            return;
        }

        $objectRepository = $this->em->getRepository($class);
        $object = $objectRepository->find($id);
        // TODO: looking for a better approach. It is because we use knp doctrine behaviour
        if ($object instanceof TranslatableInterface) {
            $object->setCurrentLocale($autoRoute->getDefault('_locale'));
        }

        $autoRoute->setContent($object);
    }

    public function getAllContent(string $classFqn)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('a')
            ->from($classFqn, 'a');
        $q = $qb->getQuery();
        $content = $q->getResult();

        return $content;
    }

    public function getIdentifier($autoRouteableContent)
    {
        $unitOfWork = $this->em->getUnitOfWork();
        $identifier = $unitOfWork->getSingleIdentifierValue($autoRouteableContent);

        return $identifier;
    }

    /**
     * Calculates the new position for redirect urls. Provides an higher number to allow route sorting.
     *
     * @param int $newRoutePosition
     *
     * @return int
     */
    private function calculateReferringRoutePosition($newRoutePosition)
    {
        return $newRoutePosition * 10 + 1;
    }

    /**
     * Sometimes $entity is a doctrine proxy and we need to retrieve the real entity FQCN.
     *
     * @param $entity
     *
     * @return string
     */
    private function getClassName($entity)
    {
        return $entity instanceof \Doctrine\ORM\Proxy\Proxy ?
            get_parent_class($entity) :
            get_class($entity);
    }
}
