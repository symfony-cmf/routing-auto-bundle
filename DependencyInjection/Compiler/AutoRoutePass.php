<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRoutePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(
            'cmf_routing_auto.service_registry'
        )) {
            return;
        }

        $builderUnitChainFactory = $container->getDefinition(
            'cmf_routing_auto.service_registry'
        );

        $types = array(
            'token_provider' => 'registerTokenProvider',
            'defunct_route_handler' => 'registerDefunctRouteHandler',
            'conflict_resolver' => 'registerConflictResolver',
        );

        foreach ($types as $type => $registerMethod) {
            $ids = $container->findTaggedServiceIds('cmf_routing_auto.'.$type);
            foreach ($ids as $id => $attributes) {
                if (!isset($attributes[0]['alias'])) {
                    throw new \InvalidArgumentException(sprintf(
                        'No "alias" specified for auto route "%s" service: "%s"',
                        str_replace('_', ' ', $type),
                        $id
                    ));
                }

                $builderUnitChainFactory->addMethodCall(
                    $registerMethod,
                    array($attributes[0]['alias'], new Reference($id))
                );
            }
        }
    }
}
