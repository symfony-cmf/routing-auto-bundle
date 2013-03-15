<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRoutePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(
            'symfony_cmf_routing_auto_route.builder_unit_chain_factory'
        )) {
            return;
        }

        $builderUnitChainFactory = $container->getDefinition(
            'symfony_cmf_routing_auto_route.builder_unit_chain_factory'
        );


        $types = array(
            'path_provider', 'exists_action', 'not_exists_action'
        );

        foreach ($types as $type) {
            $ids = $container->findTaggedServiceIds('symfony_cmf_routing_auto_route.'.$type);
            foreach ($ids as $id => $attributes) {
                if (!isset($attributes[0]['alias'])) {
                    throw new \InvalidArgumentException(sprintf(
                        'No "alias" specified for auto route "%s" service: "%s"',
                        str_replace('_', ' ', $type),
                        $id
                    ));
                }

                $builderUnitChainFactory->addMethodCall(
                    'registerAlias', 
                    array($type, $attributes[0]['alias'], $id));
            }
        }
    }
}


