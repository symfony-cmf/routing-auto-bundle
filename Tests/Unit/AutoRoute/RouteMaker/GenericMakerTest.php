<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\RouteMaker;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker\GenericMaker;

class GenericMakerTest extends \PHPUnit_Framework_TestCase
{
    protected $routeClass = 'Doctrine\ODM\PHPCR\Document\Generic';
    protected $makerClass = 'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker\GenericMaker';

    public function setUp()
    {
        $this->dm = $this->getMockBuilder(
            'Doctrine\ODM\PHPCR\DocumentManager'
        )->disableOriginalConstructor()->getMock();

        $this->routeStack =  $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->classMetadata = $this->getMockBuilder(
            'Doctrine\ODM\PHPCR\Mapping\ClassMetadata'
        )->disableOriginalConstructor()->getMock();

        $this->routeMaker = new $this->makerClass($this->dm);
    }

    // Note that this tests everything apart from ensuring
    // that the correct routes are added with addRoute, we
    // only assert that 2 routes are added.
    public function testMake()
    {
        $this->routeStack->expects($this->once())
            ->method('getFullPaths')
            ->will($this->returnValue(array(
                'test',
                'test/foo',
            )));

        $this->dm->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->routeClass)
            ->will($this->returnValue($this->classMetadata));

        $this->routeStack->expects($this->exactly(2))
            ->method('addRoute');

        // If anybody knows of a better way to do this ...
        $test = $this;
        $routeClass = $this->routeClass;
        $this->classMetadata->expects($this->exactly(2))
            ->method('setIdentifierValue')
            ->will($this->returnCallback(function ($doc, $id) use ($test, $routeClass) {
                static $i = 0;
                $expected = array('/test', '/test/foo');

                $test->assertInstanceOf($routeClass, $doc);
                $test->assertEquals($expected[$i++], $id);
            }));

        $this->routeMaker->make($this->routeStack);
    }
}
