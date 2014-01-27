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

    protected $builderServices;
    protected $resolvedBuilderServices;

    protected $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
        $this->builderServices = $this->resolvedBuilderServices = array(
            'provider' => array(),
            'exists_action' => array(),
            'not_exists_action' => array(),
            'route_maker' => array(),
        );
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
     * e.g. registerAlias('path_provider', 'specified', new PathProvider());
     *
     * @param string $type
     * @param string $alias
     * @param string $object
     */
    public function registerAlias($type, $alias, $object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(sprintf('Builder services should be objects, %s given', gettype($object)));
        }

        if (!isset($this->builderServices[$type])) {
            throw new \InvalidArgumentException(sprintf('Unknown builder service type "%s"', $type));
        }

        $this->builderServices[$type][$alias] = $object;
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

    /**
     * Return true if the given class FQN is mapped.
     *
     * @param string $classFqn
     *
     * @return boolean
     */
    public function hasMapping($classFqn)
    {
        return null !== $this->getMapping($classFqn, false);
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
        $pathProvider = $this->resolveBuilderConfig($config, 'provider', 'name');
        $existsAction = $this->resolveBuilderConfig($config, 'exists_action', 'strategy');
        $notExistsAction = $this->resolveBuilderConfig($config, 'not_exists_action', 'strategy');

        $builderUnit = new BuilderUnit(
            $pathProvider,
            $existsAction,
            $notExistsAction,
            $config
        );

        return $builderUnit;
    }

    protected function getMapping($classFqn, $throw = true)
    {
        $classFqns = class_parents($classFqn);
        $classFqns[] = $classFqn;
        $classFqns = array_reverse($classFqns);

        foreach ($classFqns as $classFqn) {
            if (isset($this->mapping[$classFqn])) {
                return $this->mapping[$classFqn];
            }
        }

        if ($throw) {
            throw new Exception\ClassNotMappedException($classFqn);
        }
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

    private function resolveBuilderConfig($builderConfig, $type, $aliasKey)
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

        if (isset($this->resolvedBuilderServices[$type][$alias])) {
            $service = $this->resolvedBuilderServices[$type][$alias];
        } else {
            if (!isset($this->builderServices[$type][$alias])) {
                throw new \RuntimeException(sprintf(
                    '"%s" class with alias "%s" requested, but this alias does not exist. Registered aliases "%s"',
                    $type,
                    $alias,
                    implode(',', array_keys($this->builderServices[$type]))
                ));
            }

            $service = $this->getBuilderService($type, $alias);

            $service->configureOptions($service->getOptionsResolver());

            $this->resolvedBuilderServices[$type][$alias] = $service;
        }

        unset($builderConfig[$type][$aliasKey]);

        return $service;
    }

    /**
     * Gets the builder service.
     *
     * @param string $type
     * @param string $alias
     */
    protected function getBuilderService($type, $alias)
    {
        return $this->builderServices[$type][$alias];
    }
}
