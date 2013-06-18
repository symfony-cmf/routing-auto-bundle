<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\PathNotExists;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathNotExists\ThrowException;

class ThrowExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->throwException = new ThrowException();
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\CouldNotFindRouteException
     */
    public function testThrowException()
    {
        $this->throwException->execute($this->routeStack);
    }
}

