<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Adapter;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use WAM\Bundle\CoreBundle\Doctrine\ORM\TranslatableEntityManager;
use WAM\Bundle\RoutingBundle\Enhancer\ContentRouteEnhancer;
use WAM\Bundle\RoutingBundle\Entity\AutoRoute;

/**
 * Adapter for ORM
 *
 * @author Noel Garcia <ngarcia@wearemarketing.com>
 */
class OrmAdapter implements AdapterInterface
{
    const TAG_NO_MULTILANG = 'no-multilang';

    /**
     * @var TranslatableEntityManager
     */
    private $em;
    private $baseRoutePath;
    private $autoRouteFqcn;
    private $autoRouteMetadata;

    /**
     * @var ContentRouteEnhancer
     */
    private $contentRouteEnhancer;

    /**
     * @param TranslatableEntityManager $em
     * @param ContentRouteEnhancer $contentRouteEnhancer
     * @param string $autoRouteFqcn The FQCN of the AutoRoute document to use
     */
    public function __construct($em, ContentRouteEnhancer $contentRouteEnhancer, $autoRouteFqcn = 'WAM\Bundle\RoutingBundle\Entity\AutoRoute')
    {
        $this->em = $em;
        $this->contentRouteEnhancer = $contentRouteEnhancer;
//        $this->baseRoutePath = $routeBasePath;

        $reflection = new \ReflectionClass($autoRouteFqcn);
        if (!$reflection->isSubclassOf('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface')) {
            throw new \InvalidArgumentException(sprintf('AutoRoute documents have to implement the AutoRouteInterface, "%s" does not.', $autoRouteFqcn));
        }

        $this->autoRouteFqcn = $autoRouteFqcn;
        $this->autoRouteMetadata = $em->getClassMetadata($autoRouteFqcn);
    }

    /**
     * {@inheritDoc}
     */
    public function getLocales($contentDocument)
    {
        //todo look for better aprochement
//        if ($this->dm->isEntityTranslatable($contentDocument)) {
//            return $this->dm->getLocalesFor($contentDocument);
//        }

        if ($contentDocument instanceof TranslatableInterface) {
            return array_keys($contentDocument->getTranslations());
        }

        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function translateObject($contentDocument, $locale)
    {
        return $contentDocument->translate($locale, false);
    }

    /**
     * {@inheritDoc}
     */
    public function generateAutoRouteTag(UriContext $uriContext)
    {
        return $uriContext->getLocale() ? : self::TAG_NO_MULTILANG;
    }

    /**
     * {@inheritDoc}
     */
    public function migrateAutoRouteChildren(AutoRouteInterface $srcAutoRoute, AutoRouteInterface $destAutoRoute)
    {
        //TODO implement this
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function removeAutoRoute(AutoRouteInterface $autoRoute)
    {
        $this->em->remove($autoRoute);
        $this->em->flush($autoRoute);
    }

    /**
     * {@inheritDoc}
     */
    public function createAutoRoute($uri, $contentDocument, $autoRouteTag)
    {
        $documentClassName = get_class($contentDocument);
        $metadata = $this->em->getClassMetadata($documentClassName);

        /** @var AutoRoute $headRoute */
        $headRoute = new $this->autoRouteFqcn();
        $headRoute->setContent($contentDocument);
        $headRoute->setName($this->guessRouteName($contentDocument));
        $headRoute->setStaticPrefix($uri);
        $headRoute->setAutoRouteTag($autoRouteTag);
        $headRoute->setType(AutoRouteInterface::TYPE_PRIMARY);
        $headRoute->setContentClass($documentClassName);
        $headRoute->setContentId($metadata->getIdentifierValues($contentDocument));

        return $headRoute;
    }

    private function guessRouteName($content)
    {
        return spl_object_hash($content);
    }

    /**
     * {@inheritDoc}
     */
    public function createRedirectRoute(AutoRouteInterface $referringAutoRoute, AutoRouteInterface $newRoute)
    {
        $referringAutoRoute->setRedirectTarget($newRoute);
        $referringAutoRoute->setPosition($this->calculateReferringRoutePosition($newRoute->getPosition()));
        $referringAutoRoute->setType(AutoRouteInterface::TYPE_REDIRECT);
        $this->em->flush($referringAutoRoute);
    }

    /**
     * Calculates the new position for redirect urls. Provides an higher number to allow route sorting
     *
     * @param int $newRoutePosition
     * @return int
     */
    private function calculateReferringRoutePosition($newRoutePosition)
    {
        return $newRoutePosition * 10 + 1;
    }

    /**
     * {@inheritDoc}
     */
    public function getRealClassName($className)
    {
        return ClassUtils::getRealClass($className);
    }

    /**
     * {@inheritDoc}
     */
    public function compareAutoRouteContent(AutoRouteInterface $autoRoute, $contentDocument)
    {
        if ($autoRoute->getContent() === $contentDocument) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getReferringAutoRoutes($contentDocument)
    {
        return $contentDocument->getRoutes();
    }

    /**
     * {@inheritDoc}
     */
    public function findRouteForUri($uri)
    {
        if ($route = $this->em->getRepository($this->autoRouteFqcn)->findOneByStaticPrefix($uri)) {
            $this->contentRouteEnhancer->resolveRouteContent($route);
        }

        return $route;
    }
}
