<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute\RouteStack\RouteStack;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dm = $this->getMockBuilder(
            'Doctrine\ODM\PHPCR\DocumentManager'
        )->disableOriginalConstructor()->getMock();;

        $this->routeStackBuilder = new Builder($this->dm);
        $this->routeStackBuilderUnit = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack\BuilderUnitInterface'
        );
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );
        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();
        $this->route1 = new \stdClass;
    }

    public function testNotExists()
    {
        $this->routeStackBuilderUnit->expects($this->once())
            ->method('pathAction')
            ->with($this->routeStack);
        $this->routeStack->expects($this->once())
            ->method('getFullPath')
            ->will($this->returnValue('test/path'));

        $this->dm->expects($this->once())
            ->method('find')
            ->with(null, '/test/path')
            ->will($this->returnValue(null));

        $this->routeStackBuilderUnit->expects($this->once())
            ->method('notExistsAction')
            ->with($this->routeStack);

        $this->routeStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));
    
        $this->routeStack->expects($this->once())
            ->method('close');

        $this->routeStackBuilder->build($this->routeStack, $this->routeStackBuilderUnit);
    }

    public function testExists()
    {
        $this->routeStackBuilderUnit->expects($this->once())
            ->method('pathAction')
            ->with($this->routeStack);

        $this->routeStack->expects($this->once())
            ->method('getFullPath')
            ->will($this->returnValue('test/path'));

        $this->routeStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));

        $this->routeStackBuilderUnit->expects($this->exactly(1))
            ->method('existsAction')
            ->with($this->routeStack);

        $this->routeStack->expects($this->once())
            ->method('close');

        // first two node paths exist, third is OK
        $this->dm->expects($this->exactly(1))
            ->method('find')
            ->with(null, '/test/path')
            ->will($this->returnCallback(function ($path) {
                static $count = 0;
                if ($count == 2) {
                    return false;
                }

                $count++;
                return new \stdClass;
            }));

        $this->routeStackBuilder->build($this->routeStack, $this->routeStackBuilderUnit);
    }
}
