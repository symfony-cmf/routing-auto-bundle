<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit;

use Symfony\Cmf\Bundle\RoutingAutoBundle\ContainerAwareFactory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder'
        )->disableOriginalConstructor()->getMock();

        $this->bucf = new ContainerAwareFactory(
            $this->builder
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
        foreach ($this->dicMap as $service) {
            $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
            $service->expects($this->any())
                ->method('getOptionsResolver')
                ->will($this->returnValue($resolver));
        }

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->bucf->setContainer($this->container);

        $this->bucf->registerAlias('provider', 'fixed', 'fixed_service_id');
        $this->bucf->registerAlias('provider', 'dynamic', 'dynamic_service_id');
        $this->bucf->registerAlias('exists_action', 'create', 'create_service_id');
        $this->bucf->registerAlias('not_exists_action', 'throw_excep', 'throw_excep_service_id');
        $this->bucf->registerAlias('provider', 'extra', 'extra_service_id');
    }

    public function provideTestLazyLoaded()
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
            ),
        );
    }

    /**
     * @dataProvider provideTestLazyLoaded
     */
    public function testLazyLoaded($config)
    {
        $dicMap = $this->dicMap;
        $callMap = array('fixed_service_id', 'create_service_id', 'throw_excep_service_id');
        $this->container->expects($this->any())
            ->method('get')
            ->with($this->callback(function ($value) use (&$callMap) {
                return in_array($value, $callMap);
            }))
            ->will($this->returnCallback(function ($serviceId) use ($dicMap) {
                return $dicMap[$serviceId];
            }));

        $this->bucf->registerMapping('FooBar/Class', $config);
        $chain = $this->bucf->getRouteStackBuilderUnitChain('FooBar/Class');
    }
}
