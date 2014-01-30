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

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builder = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder'
        )->disableOriginalConstructor()->getMock();

        $this->bucf = new Factory(
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
        foreach ($this->dicMap as $dic) {
            $optionsResolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');

            $dic->expects($this->any())
                ->method('getOptionsResolver')
                ->will($this->returnValue($optionsResolver));
        }

        $this->bucf->registerPathProvider('fixed', $this->fixedPath);
        $this->bucf->registerPathProvider('dynamic', $this->dynamicPath);
        $this->bucf->registerPathAction('exists', 'create', $this->createPath);
        $this->bucf->registerPathAction('not_exists', 'throw_excep', $this->throwExceptionPath);
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\ClassNotMappedException
     */
    public function testClassNotMappedException()
    {
        $this->bucf->getRouteStackBuilderUnitChain('stdClass');
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
        $this->markTestSkipped("TODO");
        $dicMap = $this->dicMap;

        foreach ($assertOptions as $serviceId => $assertOptions) {
            $optionsResolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
            $optionsResolver->expects($this->once())
                ->method('resolve')
                ->with($assertOptions);

            $dicMap[$serviceId]->expects($this->once())
                ->method('getOptionsResolver')
                ->will($this->returnValue($optionsResolver));
        }

        $this->bucf->registerMapping('stdClass', $config);
        $this->bucf->getRouteStackBuilderUnitChain('stdClass');

        $context = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext');
        $chain->executeChain($context);
    }
}
