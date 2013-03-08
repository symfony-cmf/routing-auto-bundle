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
            ->scalarNode('basepath')
                ->defaultValue('/cms/auto-routes')
            ->end()
            ->arrayNode('auto_route_by_class')
                ->useAttributeAsKey('class')
                ->prototype('array')
                    ->children()
                    ->scalarNode('base_path')->end()
                    ->scalarNode('route_method_name')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

