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
}
