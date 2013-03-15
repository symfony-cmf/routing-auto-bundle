<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderUnitChainFactory;

class BuilderUnitChainFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderInterface'
        );

        $this->container = $this->getMock(
            'Symfony\Component\DependencyInjection\ContainerInterface'
        );

        $this->bucf = new BuilderUnitChainFactory(
            $this->container, $this->builder
        );

        $this->fixedPath = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface');
        $this->dynamicPath = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface');
        $this->createPath = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface');
        $this->throwExceptionPath = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface');

        $this->dicMap = array(
            'fixed_service_id' => $this->fixedPath,
            'dynamic_service_id' => $this->dynamicPath,
            'create_service_id' => $this->createPath,
            'throw_excep_service_id' => $this->throwExceptionPath,
        );

        $this->bucf->registerAlias('path_provider', 'fixed', 'fixed_service_id');
        $this->bucf->registerAlias('path_provider', 'dynamic', 'dynamic_service_id');
        $this->bucf->registerAlias('exists_action', 'create', 'create_service_id');
        $this->bucf->registerAlias('not_exists_action', 'throw_excep', 'throw_excep_service_id');
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\ClassNotMappedException
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
                            'name' => 'fixed',
                            'message' => 'foobar'
                        ),
                        'exists_action' => array(
                            'strategy' => 'create'
                        ),
                        'not_exists_action' => array(
                            'strategy' => 'throw_excep',
                        ),
                    ),
                ),
                array(
                    'fixed_service_id' => array('message' => 'foobar'),
                ),
            ),
        );
    }

    /**
     * @dataProvider provideTestGetChain
     */
    public function testGetChain($config, $assertOptions)
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

        foreach ($assertOptions as $serviceId => $assertOptions) {
            $dicMap[$serviceId]->expects($this->once())
                ->method('init')
                ->with($assertOptions);
        }

        $this->bucf->registerMapping('FooBar/Class', $config);
        $this->bucf->getChain('FooBar/Class');
    }
}
