<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Compiler\AutoRoutePass;
use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass;

class CmfRoutingAutoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AutoRoutePass());
        $this->buildPhpcrCompilerPass($container);
    }

    /**
     * Creates and registers compiler passes for PHPCR-ODM mapping if both the
     * phpcr-odm and the phpcr-bundle are present.
     *
     * @param ContainerBuilder $container
     */
    private function buildPhpcrCompilerPass(ContainerBuilder $container)
    {
        if (!class_exists('Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass')
            || !class_exists('Doctrine\ODM\PHPCR\Version')
        ) {
            return;
        }

        $container->addCompilerPass(
            DoctrinePhpcrMappingsPass::createXmlMappingDriver(
                array(
                    realpath(__DIR__.'/Resources/config/doctrine-model') => 'Symfony\Cmf\Bundle\RoutingAutoBundle\Model',
                ),
                array('cmf_routing_auto.persistence.phpcr.manager_name'),
                false,
                array('CmfRoutingAutoBundle' => 'Symfony\Cmf\Bundle\RoutingAutoBundle\Model')
            )
        );
    }

}
