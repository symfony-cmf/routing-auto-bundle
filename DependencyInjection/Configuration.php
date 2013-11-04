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
        $needsNormalization = function ($v) {
            if (!is_array($v)) {
                return false;
            }

            return isset($v['option']);
        };
        $doNormalization = function ($v) {
            $value = array();
            foreach ($v['option'] as $option) {
                $value[$option['name']] = $option['value'];
            }

            return $value;
        };

        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('cmf_routing_auto')
            ->children()
            ->arrayNode('auto_route_mapping')
                ->useAttributeAsKey('class')
                ->prototype('array')
                    ->children()
                    ->arrayNode('content_path')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                            ->arrayNode('provider')
                                ->beforeNormalization()
                                    ->ifTrue($needsNormalization)
                                    ->then($doNormalization)
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('exists_action')
                                ->beforeNormalization()
                                    ->ifTrue($needsNormalization)
                                    ->then($doNormalization)
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('not_exists_action')
                                ->beforeNormalization()
                                    ->ifTrue($needsNormalization)
                                    ->then($doNormalization)
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                        ->end()
                    ->end()
                    ->arrayNode('content_name')
                        ->children()
                        ->arrayNode('provider')
                            ->beforeNormalization()
                                ->ifTrue($needsNormalization)
                                ->then($doNormalization)
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('exists_action')
                            ->beforeNormalization()
                                ->ifTrue($needsNormalization)
                                ->then($doNormalization)
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('not_exists_action')
                            ->beforeNormalization()
                                ->ifTrue($needsNormalization)
                                ->then($doNormalization)
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

