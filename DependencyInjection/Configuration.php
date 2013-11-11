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
        $treeBuilder->root('cmf_routing_auto')
            ->fixXmlConfig('auto_route_mapping')
            ->children()
                ->arrayNode('auto_route_mappings')
                    ->useAttributeAsKey('class')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('content_path')
                                ->fixXmlConfig('route_stack')
                                ->children()
                                    ->arrayNode('route_stacks')
                                        ->useAttributeAsKey('name')
                                        ->prototype('array')
                                            ->children()
                                                ->arrayNode('provider')
                                                    ->fixXmlConfig('option')
                                                    ->children()
                                                        ->scalarNode('name')->isRequired()->end()
                                                        ->arrayNode('options')
                                                            ->useAttributeAsKey('name')
                                                            ->prototype('scalar')->end()
                                                        ->end()
                                                    ->end()
                                                ->end() // provider
                                                ->arrayNode('exists_action')
                                                    ->fixXmlConfig('option')
                                                    ->children()
                                                        ->scalarNode('strategy')->isRequired()->end()
                                                        ->arrayNode('options')
                                                            ->useAttributeAsKey('name')
                                                            ->prototype('scalar')->end()
                                                        ->end()
                                                    ->end()
                                                ->end() // exists_action
                                                ->arrayNode('not_exists_action')
                                                    ->fixXmlConfig('option')
                                                    ->children()
                                                        ->scalarNode('strategy')->isRequired()->end()
                                                        ->arrayNode('options')
                                                            ->useAttributeAsKey('name')
                                                            ->prototype('scalar')->end()
                                                        ->end()
                                                    ->end()
                                                ->end() // not_exists_action
                                            ->end()
                                        ->end()
                                    ->end() // route_stacks
                                ->end()
                            ->end() // content_path
                            ->arrayNode('content_name')
                                ->children()
                                    ->arrayNode('provider')
                                        ->fixXmlConfig('option')
                                        ->children()
                                            ->scalarNode('name')->isRequired()->end()
                                            ->arrayNode('options')
                                                ->useAttributeAsKey('name')
                                                ->prototype('scalar')->end()
                                            ->end()
                                        ->end()
                                    ->end() // provider
                                    ->arrayNode('exists_action')
                                        ->fixXmlConfig('option')
                                        ->children()
                                            ->scalarNode('strategy')->isRequired()->end()
                                            ->arrayNode('options')
                                                ->useAttributeAsKey('name')
                                                ->prototype('scalar')->end()
                                            ->end()
                                        ->end()
                                    ->end() // exists_action
                                    ->arrayNode('not_exists_action')
                                        ->fixXmlConfig('option')
                                        ->children()
                                            ->scalarNode('strategy')->isRequired()->end()
                                            ->arrayNode('options')
                                                ->useAttributeAsKey('name')
                                                ->prototype('scalar')->end()
                                            ->end()
                                        ->end()
                                    ->end() // not_exists_action
                                ->end()
                            ->end() // content_name
                        ->end()
                    ->end()
                ->end() // auto_route_mappings
            ->end()
        ->end();

        return $treeBuilder;
    }
}

