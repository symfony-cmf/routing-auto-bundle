<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Adapter;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;

/**
 * Adapter for PHPCR-ODM.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class DoctrineOrmAdapter implements AdapterInterface
{
    const TAG_NO_MULTILANG = 'no-multilang';

    protected $entityManager;
    protected $autoRouteFqcn;

    /**
     * @param DocumentManager $entityManager
     * @param string          $routeBasePath Route path for all routes
     * @param string          $autoRouteFqcn The FQCN of the AutoRoute document to use
     */
    public function __construct(EntityManager $entityManager, $autoRouteFqcn = 'Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute')

    {
        $this->entityManager = $entityManager;

        $reflection = new \ReflectionClass($autoRouteFqcn);
        if (!$reflection->isSubclassOf('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface')) {
            throw new \InvalidArgumentException(sprintf('AutoRoute documents have to implement the AutoRouteInterface, "%s" does not.', $autoRouteFqcn));
        }

        $this->autoRouteFqcn = $autoRouteFqcn;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales($contentDocument)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function translateObject($contentDocument, $locale)
    {
        throw new \BadMethodCallException('Translation not supported with Doctrine ORM adapter');
    }

    /**
     * {@inheritdoc}
     */
    public function generateAutoRouteTag(UriContext $uriContext)
    {
        return self::TAG_NO_MULTILANG;
    }

    /**
     * {@inheritdoc}
     */
    public function migrateAutoRouteChildren(AutoRouteInterface $srcAutoRoute, AutoRouteInterface $destAutoRoute)
    {
        throw new \RuntimeException('TODO');
    }

    /**
     * {@inheritdoc}
     */
    public function removeAutoRoute(AutoRouteInterface $autoRoute)
    {
        $this->entityManager->remove($autoRoute);
    }

    /**
     * {@inheritdoc}
     */
    public function createAutoRoute(UriContext $uriContext, $contentDocument, $autoRouteTag)
    {
        $route = new Route();
        $route->setName(uniqid());
        $route->setStaticPrefix($uriContext->getUri());

        $this->entityManager->persist($route);

        foreach ($uriContext->getDefaults() as $key => $value) {
            $route->setDefault($key, $value);
        }


        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function createRedirectRoute(AutoRouteInterface $referringAutoRoute, AutoRouteInterface $newRoute)
    {
        $referringAutoRoute->setRedirectTarget($newRoute);
        $referringAutoRoute->setType(AutoRouteInterface::TYPE_REDIRECT);
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
        throw new \RuntimeException('TODO');
    }

    /**
     * {@inheritdoc}
     */
    public function findRouteForUri($uri, UriContext $uriContext)
    {
        return $this->entityManager
            ->getRepository(Route::class)
            ->findOneBy([
                'staticPrefix' => $uri
            ]);
    }
}
