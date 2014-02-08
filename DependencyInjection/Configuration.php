<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
            ->fixXmlConfig('mapping')
            ->children()
                ->arrayNode('mappings')
                    ->useAttributeAsKey('class')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('content_path')
                                ->beforeNormalization()
                                    ->ifTrue(function ($v) {
                                        return !isset($v['path_unit']) && !isset($v['path_units']);
                                    })
                                    ->then(function ($v) {
                                        return array(
                                            'path_units' => $v,
                                        );
                                    })
                                ->end()
                                ->fixXmlConfig('path_unit')
                                ->children()
                                    ->arrayNode('path_units')
                                        ->useAttributeAsKey('name')
                                        ->prototype('array')
                                            ->children()
                                                ->append($this->getUnitConfigOption('provider', 'name'))
                                                ->append($this->getUnitConfigOption('exists_action'))
                                                ->append($this->getUnitConfigOption('not_exists_action'))
                                            ->end()
                                        ->end()
                                    ->end() // path_units
                                ->end()
                            ->end() // content_path
                            ->arrayNode('content_name')
                                ->children()
                                    ->append($this->getUnitConfigOption('provider', 'name'))
                                    ->append($this->getUnitConfigOption('exists_action'))
                                    ->append($this->getUnitConfigOption('not_exists_action'))
                                ->end()
                            ->end() // content_name
                        ->end()
                    ->end()
                ->end() // mappings
            ->end();

        return $treeBuilder;
    }

    protected function getUnitConfigOption($name, $nameOption = 'strategy')
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
