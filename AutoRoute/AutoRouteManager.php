<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteStack;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder;
use Doctrine\Common\Util\ClassUtils;

/**
 * This class is concerned with the automatic creation of route objects.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteManager
{
    protected $factory;

    public function __construct(Factory $factory, Builder $builder)
    {
        $this->factory = $factory;
        $this->builder = $builder;
    }

    /**
     * Create or update the automatically generated route for
     * the given document.
     *
     * When this is finished it will support multiple locales.
     *
     * @param object Mapped document for which to generate the AutoRoute
     *
     * @return BuilderContext
     */
    public function updateAutoRouteForDocument($document)
    {
        $classFqn = ClassUtils::getClass($document);

        $context = new BuilderContext;
        $context->setContent($document);

        // build chain
        $builderUnitChain = $this->factory->getRouteStackBuilderUnitChain($classFqn);
        $builderUnitChain->executeChain($context);

        // persist the auto route
        $autoRouteStack = new AutoRouteStack($context);
        $builderUnit = $this->factory->getContentNameBuilderUnit($classFqn);
        $this->builder->build($autoRouteStack, $builderUnit);

        return $context;
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
