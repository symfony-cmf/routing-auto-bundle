<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderUnitChain;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This class contains the mapping information for all
 * auto-routeable classes. It uses this mapping information
 * to construct the BuilderChains on request.
 *
 * NOTE: In the future it should be relatively simple to change this
 *       to support annotations/mapping which would override settings
 *       given in the DIC config.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class BuilderUnitChainFactory
{
    protected $mapping;

    // we lazy-load the builder chains, this will allow us to support
    // addition annotation/mapping in the future.
    protected $builderUnitChains;

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
        if (!isset($this->builderUnitChains[$classFqn])) {
            $this->builderUnitChains[$classFqn] = $this->generateBuilderUnitChain($classFqn);
        }

        return $this->builderUnitChains[$classFqn];
    }

    public function hasMapping($classFqn)
    {
        // @todo: Do we need to support inheritance?
        return isset($this->mapping[$classFqn]);
    }

    protected function generateBuilderUnitChain($classFqn)
    {
        if (!isset($this->mapping[$classFqn])) {
            throw new Exception\ClassNotMappedException($classFqn);
        }

        $config = $this->mapping[$classFqn];
        $chain = new BuilderUnitChain($this->builder);

        foreach ($config as $builderName => $builderConfig) {
            $pathProvider = $this->getBuilderService($builderConfig, 'path_provider', 'name');
            $existsAction = $this->getBuilderService($builderConfig, 'exists_action', 'strategy');
            $notExistsAction = $this->getBuilderService($builderConfig, 'not_exists_action', 'strategy');

            $builderUnit = new BuilderUnit(
                $pathProvider,
                $existsAction,
                $notExistsAction
            );

            $chain->addBuilderUnit($builderName, $builderUnit);
        }
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

        return $this->container->get($serviceId);
    }
}
