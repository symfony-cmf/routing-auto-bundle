<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder'
        )->disableOriginalConstructor()->getMock();

        $this->container = $this->getMock(
            'Symfony\Component\DependencyInjection\ContainerInterface'
        );

        $this->bucf = new Factory(
            $this->container, $this->builder
        );

        $this->fixedPath = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface'
        );
        $this->dynamicPath = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface'
        );
        $this->createPath = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface'
        );
        $this->throwExceptionPath = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface'
        );

        $this->dicMap = array(
            'fixed_service_id' => $this->fixedPath,
            'dynamic_service_id' => $this->dynamicPath,
            'create_service_id' => $this->createPath,
            'throw_excep_service_id' => $this->throwExceptionPath,
        );

        $this->bucf->registerAlias('provider', 'fixed', 'fixed_service_id');
        $this->bucf->registerAlias('provider', 'dynamic', 'dynamic_service_id');
        $this->bucf->registerAlias('exists_action', 'create', 'create_service_id');
        $this->bucf->registerAlias('not_exists_action', 'throw_excep', 'throw_excep_service_id');
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\ClassNotMappedException
     */
    public function testClassNotMappedException()
    {
        $this->bucf->getRouteStackBuilderUnitChain('FooBar');
    }

    public function provideTestGetChain()
    {
        return array(
            array(
                array(
                    'content_path' => array(
                        'path_units' => array(
                            'base' => array(
                                'provider' => array(
                                    'name' => 'fixed',
                                    'options' => array(
                                        'message' => 'foobar',
                                    ),
                                ),
                                'exists_action' => array(
                                    'strategy' => 'create',
                                    'options' => array(),
                                ),
                                'not_exists_action' => array(
                                    'strategy' => 'throw_excep',
                                    'options' => array(),
                                ),
                            ),
                        ),
                    ),
                    'content_name' => array(
                        'provider' => array(
                            'name' => 'fixed',
                            'options' => array(
                                'message' => 'barfoo',
                            ),
                        ),
                        'exists_action' => array(
                            'strategy' => 'create',
                            'options' => array(),
                        ),
                        'not_exists_action' => array(
                            'strategy' => 'throw_excep',
                            'options' => array(),
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
        $this->bucf->getRouteStackBuilderUnitChain('FooBar/Class');
    }
}
