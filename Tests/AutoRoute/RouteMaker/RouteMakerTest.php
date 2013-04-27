<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute\RouteMaker;

class RouteMakerTest extends GenericMakerTest
{
    protected $routeClass = 'Symfony\Cmf\Bundle\RoutingBundle\Document\Route';
    protected $makerClass = 'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker\RouteMaker';

    public function testMake()
    {
        $this->routeMaker->init(array(
            'defaults' => array('_controller' => 'foobar')
        ));

        $this->routeStack->expects($this->once())
            ->method('getFullPaths')
            ->will($this->returnValue(array(
                'test',
                'test/foo',
            )));

        $test = $this;
        $this->routeStack->expects($this->exactly(2))
            ->method('addRoute')
            ->will($this->returnCallback(function ($doc) use ($test) {
                static $i = 0;
                $expected = array('/test', '/test/foo');

                $test->assertInstanceOf(
                    'Symfony\Cmf\Bundle\RoutingBundle\Document\Route', 
                    $doc
                );

                $test->assertEquals($expected[$i++], $doc->getId());
                $defaults = $doc->getDefaults();
                $test->assertTrue(isset($defaults['_controller']));
                $test->assertEquals('foobar', $defaults['_controller']);
            }));

        $this->routeMaker->make($this->routeStack);
    }
}
