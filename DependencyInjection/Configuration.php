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
            ->fixXmlConfig('mapping')
            ->children()
                ->arrayNode('mappings')
                    ->useAttributeAsKey('class')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('content_path')
                                ->fixXmlConfig('path_unit')
                                ->children()
                                    ->arrayNode('path_units')
                                        ->useAttributeAsKey('name')
                                        ->prototype('array')
                                            ->children()
                                                ->append($this->getBuilderUnitConfigOption('provider', 'name'))
                                                ->append($this->getBuilderUnitConfigOption('exists_action'))
                                                ->append($this->getBuilderUnitConfigOption('not_exists_action'))
                                            ->end()
                                        ->end()
                                    ->end() // path_units
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
                ->end() // mappings
            ->end();

        return $treeBuilder;
    }

    protected function getBuilderUnitConfigOption($name, $nameOption = 'strategy')
    {
        $builder = new TreeBuilder();
        $node = $builder->root($name);

        $node
            ->fixXmlConfig('option')
            ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return is_string($v);
                })
                ->then(function ($v) use ($nameOption) {
                    return array(
                        $nameOption => $v,
                        'options' => array(),
                    );
                })
            ->end()
            ->beforeNormalization()
                ->ifTrue(function ($v) use ($nameOption) {
                    return !isset($v[$nameOption]);
                })
                ->then(function ($v) use ($nameOption) {
                    return array(
                        $nameOption => $v[0],
                        'options' => isset($v[1]) ? $v[1] : array(),
                    );
                })
            ->end()
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

