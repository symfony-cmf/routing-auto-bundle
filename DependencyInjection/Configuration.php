<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('symfony_cmf_routing_auto');

        $rootNode
            ->children()
                ->scalarNode('route_base_path')
                    ->defaultValue(null)
                ->end()
                ->arrayNode('auto_route_mapping')
                    ->useAttributeAsKey('class')
                    ->prototype('array')
                        ->children()
                        ->arrayNode('content_path')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->arrayNode('provider')
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
                        ->arrayNode('content_name')
                            ->children()
                                ->arrayNode('provider')
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
                ->end()
            ->end();

        return $treeBuilder;
    }
}

