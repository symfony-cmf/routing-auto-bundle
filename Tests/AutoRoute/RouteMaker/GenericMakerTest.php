<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute\RouteMaker;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker\GenericMaker;

class GenericMakerTest extends \PHPUnit_Framework_TestCase
{
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

        $this->routeMaker = new GenericMaker($this->dm);
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
            ->with('Doctrine\ODM\PHPCR\Document\Generic')
            ->will($this->returnValue($this->classMetadata));

        $this->routeStack->expects($this->exactly(2))
            ->method('addRoute');

        // If anybody knows of a better way to do this ...
        $test = $this;
        $this->classMetadata->expects($this->exactly(2))
            ->method('setIdentifierValue')
            ->will($this->returnCallback(function ($doc, $id) use ($test) {
                static $i = 0;
                $expected = array('/test', '/test/foo');

                $test->assertInstanceOf('Doctrine\ODM\PHPCR\Document\Generic', $doc);
                $test->assertEquals($expected[$i++], $id);
            }));

        $this->routeMaker->make($this->routeStack);
    }
}
