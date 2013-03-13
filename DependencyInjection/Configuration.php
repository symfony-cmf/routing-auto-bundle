<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * Returns the config tree builder.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('symfony_cmf_routing_auto_route')
            ->children()
            ->arrayNode('auto_route_definitions')
                ->useAttributeAsKey('class')
                ->prototype('array')
                    ->children()
                    ->arrayNode('chain')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                            ->arrayNode('path_provider')
                                ->useAttributeAsKey('key')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('exists_action')
                                ->useAttributeAsKey('key')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('not_exists_action')
                                ->useAttributeAsKey('key')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

