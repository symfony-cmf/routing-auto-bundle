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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use WAM\Bundle\RoutingBundle\Enhancer\ContentRouteEnhancer;
use WAM\Bundle\RoutingBundle\Entity\AutoRoute;
use WAM\Bundle\RoutingBundle\Model\SeoMetaReadInterface;

/**
 * Adapter for ORM.
 *
 * @author Noel Garcia <ngarcia@wearemarketing.com>
 * @author Mauro Casula <mcasula@wearemarketing.com>
 * @author David Velasco <dvelasco@wearemarketing.com>
 */
class OrmAdapter implements AdapterInterface
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
     * @var ContentRouteEnhancer
     */
    private $contentRouteEnhancer;

    /**
     * @param EntityManagerInterface $em
     * @param ContentRouteEnhancer   $contentRouteEnhancer
     * @param string                 $autoRouteFqcn        The FQCN of the AutoRoute document to use
     */
    public function __construct(EntityManagerInterface $em, ContentRouteEnhancer $contentRouteEnhancer, $autoRouteFqcn = 'WAM\Bundle\RoutingBundle\Entity\AutoRoute')
    {
        $this->em = $em;
        $this->contentRouteEnhancer = $contentRouteEnhancer;
//        $this->baseRoutePath = $routeBasePath;

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
        //todo look for better approach
        if ($contentDocument instanceof TranslatableInterface) {
            return array_keys($contentDocument->getTranslations()->toArray());
        }

        return array();
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
        //implementation is not needed for orm
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
    public function createAutoRoute(UriContext $uri, $autoRouteTag)
    {
        $seoMetaData = array('title' => '', 'description' => '', 'metaKeywords' => '');
        $contentDocument = $uri->getSubject();

        foreach ($contentDocument->getRoutes() as $item) {
            if ($this->isPrimaryAndSameLocale($autoRouteTag, $item, $contentDocument)) {
                $this->updateSeoData($contentDocument, $item);

                $seoMetaData = $item->getSeoMetaData();
            }
        }

        $documentClassName = get_class($contentDocument);
        /** @var ClassMetadata $metadata */
        $metadata = $this->em->getClassMetadata($documentClassName);
        $id = $metadata->getIdentifierValues($contentDocument);
        $defaults = $uri->getDefaults();

        /** @var AutoRoute $headRoute */
        $headRoute = new $this->autoRouteFqcn();
        $headRoute->setContent($contentDocument);
        $headRoute->setStaticPrefix($uri->getUri());
        $headRoute->setAutoRouteTag($autoRouteTag);
        $headRoute->setType(AutoRouteInterface::TYPE_PRIMARY);
        $headRoute->setContentClass($documentClassName);
        $headRoute->setContentId($id);
        $headRoute->setDefaults($defaults);
        $headRoute->setSeoMetaData($seoMetaData);

        //Route name is compound by: table name, row id, locale if present, type, epoch time
        $routeNameParts = array_merge(
            array($metadata->getTableName()),
            $id ? array_values($id) : array(self::ID_PLACEHOLDER)
        );

        if (!empty($defaults['type'])) {
            $routeNameParts[] = $defaults['type'];
            $headRoute->setRequirement('type', $defaults['type']);
        }

        $headRoute->setCanonicalName(implode('_', $routeNameParts));
        if (self::TAG_NO_MULTILANG != $autoRouteTag) {
            $headRoute->setRequirement(self::REQUEST_LOCALE_ATTRIBUTE, $autoRouteTag);
            $headRoute->setDefault(self::REQUEST_LOCALE_ATTRIBUTE, $autoRouteTag);
            $routeNameParts[] = $autoRouteTag;
        }

        $routeNameParts[] = time();
        $headRoute->setName(implode('_', $routeNameParts));

        return $headRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function createRedirectRoute(AutoRouteInterface $referringAutoRoute, AutoRouteInterface $newRoute)
    {
        // check if $newRoute already exists
        $route = $this->em->getRepository($this->autoRouteFqcn)->findOneByStaticPrefix($newRoute->getStaticPrefix());

        if ($route) {
            // in case it's a redirection, remove redirection's defaults
            $defaults = $route->getDefaults();
            unset($defaults['_controller']);
            unset($defaults['route']);
            unset($defaults['permanent']);
            $route->setDefaults($defaults);
            $this->em->flush($route);
        }

        $referringAutoRoute->setRedirectTarget($newRoute);
        $referringAutoRoute->setPosition($this->calculateReferringRoutePosition($newRoute->getPosition()));
        $referringAutoRoute->setType(AutoRouteInterface::TYPE_REDIRECT);

        //WARNING http://doctrine-orm.readthedocs.org/en/latest/reference/events.html#postflush
        //según la documentación de doctrine no se debe invocar a em::flush() desde el evento postFlush,
        //pero al parecer funciona bien si em::flush() recibe como argumento la entidad a persistir
        $this->em->flush($referringAutoRoute);
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
        if ($route = $this->em->getRepository($this->autoRouteFqcn)->findOneByStaticPrefix($uri)) {
            $this->contentRouteEnhancer->resolveRouteContent($route);
        }

        return $route;
    }

    /**
     * @param $autoRouteTag
     * @param $item
     *
     * @return bool
     */
    protected function isPrimaryAndSameLocale($autoRouteTag, $item)
    {
        return AutoRouteInterface::TYPE_PRIMARY == $item->getType() && $autoRouteTag == $item->getTag();
    }

    /**
     * @param $contentDocument
     * @param $item
     */
    private function updateSeoData($contentDocument, $item)
    {
        if ($contentDocument instanceof SeoMetaReadInterface) {
            if ($item->getSeoMetaData()['title'] != $contentDocument->getSeoTitle()) {
                $item->getSeoMetaData()['title'] = $contentDocument->getSeoTitle();
            }

            if ($item->getSeoMetaData()['description'] != $contentDocument->getSeoDescription()) {
                $item->getSeoMetaData()['description'] = $contentDocument->getSeoDescription();
            }

            if ($item->getSeoMetaData()['metaKeywords'] != $contentDocument->getSeoKeywords()) {
                $item->getSeoMetaData()['metaKeywords'] = $contentDocument->getSeoKeywords();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function compareAutoRouteLocale(AutoRouteInterface $autoRoute, $locale)
    {
        $autoRouteLocale = $autoRoute->getLocale();
        if ($autoRouteLocale === self::TAG_NO_MULTILANG) {
            $autoRouteLocale = null;
        }

        return $autoRouteLocale === $locale;
    }
}
