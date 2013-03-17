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
    );

    protected $container;
    protected $builder;

    public function __construct(ContainerInterface $container, Builder $builder)
    {
        $this->container = $container;
        $this->builder = $builder;
    }

    public function registerMapping($classFqn, $mapping)
    {
        $this->validateMapping($classFqn, $mapping);
        $this->mapping[$classFqn] = $mapping;
    }

    public function registerAlias($type, $alias, $id)
    {
        if (!isset($this->serviceIds[$type])) {
            throw new \RuntimeException(sprintf('Unknown service ID type "%s"', $type));
        }

        $this->serviceIds[$type][$alias] = $id;
    }

    public function getRouteStackBuilderUnitChain($classFqn)
    {
        if (!isset($this->routeStackChains[$classFqn])) {
            $this->routeStackChains[$classFqn] = $this->generateRouteStackChain($classFqn);
        }

        return $this->routeStackChains[$classFqn];
    }

    public function getAutoRouteMaker($classFqn)
    {
        if (!isset($this->autoRouteMakers[$classFqn])) {
            $this->autoRouteMakers[$classFqn] = $this->generateAutoRouteMaker($classFqn);
        }

        return $this->autoRouteMakers[$classFqn];
    }

    public function hasMapping($classFqn)
    {
        // @todo: Do we need to support inheritance?
        return isset($this->mapping[$classFqn]);
    }

    protected function generateRouteStackChain($classFqn)
    {
        $mapping = $this->getMapping($classFqn);

        $routeStackChain = new BuilderUnitChain($this->builder);

        foreach ($mapping['content_path'] as $builderName => $builderConfig) {
            $builderUnit = $this->generateBuilderUnit($builderConfig);
            $routeStackChain->addBuilderUnit($builderName, $builderUnit);
        }

        return $routeStackChain;
    }

    protected function generateAutoRouteMaker($classFqn)
    {
        $mapping = $this->getMapping($classFqn);
        $unit = $this->generateBuilderUnit($mapping['content_name']);

        $arm = new AutoRouteMaker($this->builder);

        return $arm;
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
            return isset($mapping['content_name']['exists']); 
        });
        $exists('content_name/not_exists', function ($mapping) { 
            return isset($mapping['content_name']['not_exists']); 
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
        $service->init($builderConfig[$type]);

        return $service;
    }
}
