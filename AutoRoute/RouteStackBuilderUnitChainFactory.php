<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStackChain;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Hmm.. the role of this class has changed. It should now
 * take care both the RouteStack (content path) and the
 * Route Content (content name).
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class RouteStackBuilderUnitChainFactory
{
    protected $mapping;

    // we lazy-load the builder chains, this will allow us to support
    // addition annotation/mapping in the future.
    protected $routeStackChains;

    protected $serviceIds = array(
        'path_provider' => array(),
        'exists_action' => array(),
        'not_exists_action' => array(),
    );

    protected $container;
    protected $builder;

    public function __construct(ContainerInterface $container, BuilderInterface $builder)
    {
        $this->container = $container;
        $this->builder = $builder;
    }

    public function registerMapping($classFqn, $config)
    {
        $this->mapping[$classFqn] = $config;
    }

    public function registerAlias($type, $alias, $id)
    {
        if (!isset($this->serviceIds[$type])) {
            throw new \RuntimeException(sprintf('Unknown service ID type "%s"', $type));
        }

        $this->serviceIds[$type][$alias] = $id;
    }

    public function getChain($classFqn)
    {
        if (!isset($this->routeStackChains[$classFqn])) {
            $this->routeStackChains[$classFqn] = $this->generateRouteStackChain($classFqn);
        }

        return $this->routeStackChains[$classFqn];
    }

    public function hasMapping($classFqn)
    {
        // @todo: Do we need to support inheritance?
        return isset($this->mapping[$classFqn]);
    }

    protected function generateRouteStackChain($classFqn)
    {
        if (!isset($this->mapping[$classFqn])) {
            throw new Exception\ClassNotMappedException($classFqn);
        }

        $config = $this->mapping[$classFqn];
        $routeStackChain = new RouteStackChain($this->builder);

        foreach ($config as $builderName => $builderConfig) {
            $pathProvider = $this->getBuilderService($builderConfig, 'provider', 'name');
            $existsAction = $this->getBuilderService($builderConfig, 'exists_action', 'strategy');
            $notExistsAction = $this->getBuilderService($builderConfig, 'not_exists_action', 'strategy');

            $stackBuilder = new RouteStackBuilder(
                $pathProvider,
                $existsAction,
                $notExistsAction
            );

            $routeStackChain->addBuilder($builderName, $stackBuilder);
        }

        return $chain;
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
            throw new \RuntimeException(sprintf('"%s" class with alias "%s" requested, but this alias does not exist.',
                $type,
                $alias
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
