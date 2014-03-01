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

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\MappingFactory;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\Dumper\PhpDumper;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnitChain;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnit;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class Factory
{
    /** @var MappingFactory */
    protected $mappingFactory;

    // we lazy-load the builder chains, this will allow us to support
    // addition annotation/mapping in the future.
    protected $routeStackChains;

    protected $serviceIds = array(
        'provider' => array(),
        'exists_action' => array(),
        'not_exists_action' => array(),
        'route_maker' => array(),
    );

    protected $container;
    /** @var Builder */
    protected $builder;
    /** @var LoaderInterface *
    protected $loader;
    protected $options = array();*/

    public function __construct(MappingFactory $mappingFactory, /*LoaderInterface $loader, */ContainerInterface $container, Builder $builder/*, array $options = array()*/)
    {
        $this->container = $container;
        $this->builder = $builder;
        $this->mappingFactory = $mappingFactory;
        /*$this->loader = $loader;
        $this->options = array_replace(array(
            // false -> caching is disabled
            'cache_dir' => false,
            'debug' => false,
            'mapping_cache_class' => 'ProjectAutoRouteMappingFactory',
            'resources' => array(),
        ), $options);*/
    }

    /**
     * Register an alias for a service ID of the specified type.
     *
     * e.g. registerAlias('path_provider', 'specified', 'cmf_[...]');
     *
     * @param string $type
     * @param string $alias
     * @param string $id
     */
    public function registerAlias($type, $alias, $id)
    {
        if (!isset($this->serviceIds[$type])) {
            throw new \RuntimeException(sprintf('Unknown service ID type "%s"', $type));
        }

        $this->serviceIds[$type][$alias] = $id;
    }

    /**
     * Creates the route stack builder chain for the given class FQN.
     *
     * The RouteStackBuilderUnitChain will provide all the route components
     * for the content path.
     *
     * Note that we cache it.
     *
     * @return RouteStackBuilderUnitChain
     */
    public function getRouteStackBuilderUnitChain($classFqn)
    {
        if (!isset($this->routeStackChains[$classFqn])) {
            $this->routeStackChains[$classFqn] = $this->generateRouteStackChain($classFqn);
        }

        return $this->routeStackChains[$classFqn];
    }

    /**
     * Return the build unit which will generate the content name
     * route for the given class FQN.
     *
     * @param string $classFqn
     *
     * @return BuilderUnit
     */
    public function getContentNameBuilderUnit($classFqn)
    {
        if (!isset($this->contentNameBuilderUnits[$classFqn])) {
            $mapping = $this->getMappingFactory()->getMappingsForClass($classFqn);
            $this->contentNameBuilderUnits[$classFqn] = $this->generateBuilderUnit(
                $mapping['content_name']
            );
        }

        return $this->contentNameBuilderUnits[$classFqn];
    }

    protected function getMappingFactory()
    {
        return $this->mappingFactory;
        /* TODO create native caching
        if (null !== $this->mappingFactory) {
            return $this->mappingFactory;
        }

        $mappingFactory = null;
        $getMappingFactory = function () use (&$mappingFactory) {
            if (null === $mappingFactory) {
                $mappingFactory = new MappingFactory();
                foreach ($this->options['resources'] as $resource) {
                    $mappingFactory->addMappings($this->loader->load($resource));
                }
            }

            return $mappingFactory;
        }

        // caching disabled
        if (false === $this->options['cache_dir']) {
            return $this->mappingFactory = $getMappingFactory();
        }

        // caching
        $class = $this->options['mapping_cache_class'];
        $cache = new ConfigCache($this->options['cache_dir'].'/'.$class.'php', $this->options['debug']);
        if (!$cache->isFresh()) {
            // regenerate cache
            $dumper = new PhpDumper($getMappingFactory());

            $cache->write($dumper->dump(array(
                'class' => $class,
            )));
        }

        require_once $cache;

        return $this->mappingFactory = new $class;*/
    }

    protected function generateRouteStackChain($classFqn)
    {
        $mapping = $this->getMappingFactory()->getMappingsForClass($classFqn);

        $routeStackChain = new BuilderUnitChain($this->builder);

        foreach ($mapping['content_path']['path_units'] as $builderName => $builderConfig) {
            $builderUnit = $this->generateBuilderUnit($builderConfig);
            $routeStackChain->addBuilderUnit($builderName, $builderUnit);
        }

        return $routeStackChain;
    }

    protected function generateBuilderUnit($config)
    {
        $pathProvider = $this->getBuilderService($config, 'provider', 'name');
        $existsAction = $this->getBuilderService($config, 'exists_action', 'strategy');
        $notExistsAction = $this->getBuilderService($config, 'not_exists_action', 'strategy');

        $builderUnit = new BuilderUnit(
            $pathProvider,
            $existsAction,
            $notExistsAction
        );

        return $builderUnit;
    }

    private function getBuilderService($builderConfig, $type, $aliasKey)
    {
        if (!isset($builderConfig[$type])) {
            throw new \RuntimeException(sprintf('Builder config has not defined "%s": %s',
                $type,
                print_r($builderConfig, true)
            ));
        }

        if (!isset($builderConfig[$type][$aliasKey])) {
            throw new \RuntimeException(sprintf('Builder config has not alias key "%s" for "%s": %s',
                $aliasKey,
                $type,
                print_r($builderConfig[$type], true)
            ));
        }

        $alias = $builderConfig[$type][$aliasKey];

        if (!isset($this->serviceIds[$type][$alias])) {
            throw new \RuntimeException(sprintf(
                '"%s" class with alias "%s" requested, but this alias does not exist. Registered aliases "%s"',
                $type,
                $alias,
                implode(',', array_keys($this->serviceIds[$type]))
            ));
        }

        $serviceId = $this->serviceIds[$type][$alias];

        // NOTE: Services must always be defined as scope=prototype for them
        //       to be stateless (which is good here)
        $service = $this->container->get($serviceId);
        unset($builderConfig[$type][$aliasKey]);
        $service->init($builderConfig[$type]['options']);

        return $service;
    }
}
