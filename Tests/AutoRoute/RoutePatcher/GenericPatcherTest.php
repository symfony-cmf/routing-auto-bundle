<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute\RouteMaker;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RoutePatcher\GenericPatcher;

class GenericPatcherTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->classMetadata = $this->getMockBuilder(
            'Doctrine\ODM\PHPCR\Mapping\ClassMetadata'
        )->disableOriginalConstructor()->getMock();

        $this->genericPatcher = new GenericPatcher($this->dm);
        $this->doc2 = new \stdClass;
    }

    public function testMakeRoutes()
    {
        $testCase = $this;

        $this->routeStack->expects($this->once())
            ->method('getFullPaths')
            ->will($this->returnValue(array(
                'foo/bar',
                'foo/bar/boo',
            )));

        $this->dm->expects($this->at(0))
            ->method('find')
            ->with(null, '/foo/bar')
            ->will($this->returnValue($this->doc2));

        $this->dm->expects($this->at(1))
            ->method('find')
            ->with(null, '/foo/bar/boo')
            ->will($this->returnValue(null));

        $this->dm->expects($this->once())
            ->method('getClassMetadata')
            ->with('Doctrine\ODM\PHPCR\Document\Generic')
            ->will($this->returnValue($this->classMetadata));

        $this->classMetadata->expects($this->once())
            ->method('setIdentifierValue')
            ->will($this->returnCallback(function ($doc, $id) use ($testCase) {
                $testCase->assertInstanceOf(
                    'Doctrine\ODM\PHPCR\Document\Generic',
                    $doc
                );
                $testCase->assertEquals('/foo/bar/boo', $id);
            }));

        $this->routeStack->expects($this->exactly(2))
            ->method('addRoute');

        $this->genericPatcher->patch($this->routeStack);
    }
}
