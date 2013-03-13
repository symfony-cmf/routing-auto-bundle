<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\AutoRouteManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderUnitChainFactory;

class BuilderUnitChainFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderInterface'
        );

        $this->container = $this->getMock(
            'Symfony\Component\DependencyInjection\ContainerInterface'
        );

        $this->bucf = new BuilderUnitChainFactory(
            $this->container, $this->builder
        );

        $this->dicMap = array(
            'fixed_service_id' => $this->getMock('Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathProviderInterface'),
            'dynamic_service_id' => $this->getMock('Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathProviderInterface'),
            'create_service_id' => $this->getMock('Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathActionInterface'),
            'throw_excep_service_id' => $this->getMock('Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathActionInterface'),
        );

        $this->bucf->registerAlias('path_provider', 'fixed', 'fixed_service_id');
        $this->bucf->registerAlias('path_provider', 'dynamic', 'dynamic_service_id');
        $this->bucf->registerAlias('exists_action', 'create', 'create_service_id');
        $this->bucf->registerAlias('not_exists_action', 'throw_excep', 'throw_excep_service_id');
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\Exception\ClassNotMappedException
     */
    public function testClassNotMappedException()
    {
        $this->bucf->getChain('FooBar');
    }

    public function provideTestGetChain()
    {
        return array(
            array(
                array(
                    'base' => array(
                        'path_provider' => array(
                            'name' => 'fixed'
                        ),
                        'exists_action' => array(
                            'strategy' => 'create'
                        ),
                        'not_exists_action' => array(
                            'strategy' => 'throw_excep'
                        ),
                    ),
                ),
            )
        );
    }

    /**
     * @dataProvider provideTestGetChain
     */
    public function testGetChain($config)
    {
        $this->bucf->registerAlias('path_provider', 'fixed', 'fixed_service_id');
        $this->bucf->registerAlias('path_provider', 'dynamic', 'dynamic_service_id');
        $this->bucf->registerAlias('exists_action', 'create', 'create_service_id');
        $this->bucf->registerAlias('not_exists_action', 'throw_excep', 'throw_excep_service_id');

        $dicMap = $this->dicMap;
        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($serviceId) use ($dicMap) {
                return $dicMap[$serviceId];
            }));

        $this->bucf->registerMapping('FooBar/Class', $config);
        $this->bucf->getChain('FooBar/Class');
    }
}
