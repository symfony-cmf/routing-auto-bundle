<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteStack;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Driver\DriverInterface;

/**
 * This class is concerned with the automatic creation of route objects.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteManager
{
    protected $factory;
    protected $driver;

    public function __construct(DriverInterface $driver, Factory $factory, Builder $builder)
    {
        $this->factory = $factory;
        $this->builder = $builder;
        $this->driver = $driver;
    }

    /**
     * Create or update the automatically generated route for
     * the given document.
     *
     * When this is finished it will support multiple locales.
     *
     * @param object Mapped document for which to generate the AutoRoute
     *
     * @return BuilderContext[]
     */
    public function updateAutoRouteForDocument($document)
    {
        $classFqn = ClassUtils::getClass($document);
        $locales = $this->driver->getLocales($document) ? : array(null);

        $contexts = array();

        foreach ($locales as $locale) {
            if (null !== $locale) {
                $document = $this->driver->translateObject($document, $locale);
            }

            $context = new BuilderContext;

            $context->setContent($document);
            $context->setLocale($locale);

            // build path elements
            $builderUnitChain = $this->factory->getRouteStackBuilderUnitChain($classFqn);
            $builderUnitChain->executeChain($context);

            // persist the content name element (the autoroute)
            $autoRouteStack = new AutoRouteStack($context);
            $builderUnit = $this->factory->getContentNameBuilderUnit($classFqn);
            $this->builder->build($autoRouteStack, $builderUnit);

            $contexts[] = $context;
        }

        return $contexts;
    }

    /**
     * Remove all auto routes associated with the given document.
     *
     * @param object $document Mapped document
     *
     * @todo: Test me
     *
     * @return array Array of removed routes
     */
    public function removeAutoRoutesForDocument($document)
    {
        throw new \Exception('Implement me??');
    }

    /**
     * Return true if the given document is mapped with AutoRoute
     *
     * @param object $document Document
     *
     * @return boolean
     */
    public function isAutoRouteable($document)
    {
        return $this->factory->hasMapping(ClassUtils::getClass($document));
    }
}
