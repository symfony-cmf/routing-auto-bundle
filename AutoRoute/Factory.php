<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnitChain;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnit;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class Factory
{
    protected $mapping;

    // we lazy-load the builder chains, this will allow us to support
    // addition annotation/mapping in the future.
    protected $routeStackChains;

    protected $serviceIds = array(
        'provider' => array(),
        'exists_action' => array(),
        'not_exists_action' => array(),
        'auto_route_changed' => array(),
        'route_maker' => array(),
    );

    protected $container;
    protected $builder;

    public function __construct(ContainerInterface $container, Builder $builder)
    {
        $this->container = $container;
        $this->builder = $builder;
    }

    /**
     * Register an auto route mapping for the given class.
     *
     * @param string $classFqn Class to map
     * @param array  $mapping  Mapping configuration
     */
    public function registerMapping($classFqn, $mapping)
    {
        $this->validateMapping($classFqn, $mapping);
        $this->mapping[$classFqn] = $mapping;
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
            $mapping = $this->getMapping($classFqn);
            $this->contentNameBuilderUnits[$classFqn] = $this->generateBuilderUnit(
                $mapping['content_name']
            );
        }

        return $this->contentNameBuilderUnits[$classFqn];
    }

    public function getAutoRouteChangedStrategies($classFqn)
    {
        if (!isset($this->autoRouteChangedStrategies[$classFqn])) {
            $mapping = $this->getMapping($classFqn);

            if (isset($mapping['content_changed'])) {
                $this->autoRouteChangedStrategies[$classFqn] = $this->generateAutoRouteChangedStrategies(
                    $mapping['content_changed']
                );
            } else {
                $this->autoRouteChangedStrategies[$classFqn] = array();
            }
        }
    }

    protected function generateAutoRouteChangedStrategies()
    {
        throw new \Exception('I am here.');

        $strategies = array();
        foreach ($this->serviceIds['auto_route_changed'] as $id) {
            $strategies[] = $this->container->get($id);
        }

        return $strategies;
    }

    /**
     * Return true if the given class FQN is mapped.
     *
     * @param string $classFqn
     *
     * @return boolean
     */
    public function hasMapping($classFqn)
    {
        // @todo: Do we need to support inheritance?
        return isset($this->mapping[$classFqn]);
    }

    /**
     * Return all the mapping data
     *
     * @return array
     */
    public function getMappings()
    {
        return $this->mapping;
    }

    protected function generateRouteStackChain($classFqn)
    {
        $mapping = $this->getMapping($classFqn);

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

    protected function getMapping($classFqn)
    {
        if (!isset($this->mapping[$classFqn])) {
            throw new Exception\ClassNotMappedException($classFqn);
        }

        $mapping = $this->mapping[$classFqn];

        return $mapping;
    }

    private function validateMapping($classFqn, $mapping)
    {
        $exists = function ($name, $check) use ($classFqn, $mapping) {
            if (!$check($mapping)) {
                throw new \RuntimeException(sprintf(
                    '%s not defined in mapping for class "%s": %s', 
                    $name, 
                    $classFqn,
                    print_r($mapping, true)
                ));
            }
        };

        $exists('content_path', function ($mapping) { 
            return isset($mapping['content_path']); 
        });
        $exists('content_name', function ($mapping) { 
            return isset($mapping['content_name']); 
        });
        $exists('content_name/provider', function ($mapping) { 
            return isset($mapping['content_name']['provider']); 
        });
        $exists('content_name/exists', function ($mapping) { 
            return isset($mapping['content_name']['exists_action']); 
        });
        $exists('content_name/not_exists', function ($mapping) { 
            return isset($mapping['content_name']['not_exists_action']); 
        });
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
