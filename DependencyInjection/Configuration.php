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
                                        ->append($this->getBuilderUnitConfigOption('provider', 'name'))
                                        ->append($this->getBuilderUnitConfigOption('exists_action'))
                                        ->append($this->getBuilderUnitConfigOption('not_exists_action'))
                                    ->end()
                                ->end()
                            ->end() // content_path
                            ->arrayNode('content_name')
                                ->children()
                                    ->append($this->getBuilderUnitConfigOption('provider', 'name'))
                                    ->append($this->getBuilderUnitConfigOption('exists_action'))
                                    ->append($this->getBuilderUnitConfigOption('not_exists_action'))
                                ->end()
                            ->end() // content_name
                        ->end()
                    ->end()
                ->end() // auto_route_mapping
            ->end();

        return $treeBuilder;
    }

    protected function getBuilderUnitConfigOption($name, $nameOption = 'strategy')
    {
        $builder = new TreeBuilder();
        $node = $builder->root($name);

        $node
            ->fixXmlConfig('option')
            ->children()
                ->scalarNode($nameOption)->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('options')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $node;
    }
}

