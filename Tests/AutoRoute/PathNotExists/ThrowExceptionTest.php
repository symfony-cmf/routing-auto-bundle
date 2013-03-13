<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\AutoRoute\PathNotExists;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathNotExists\ThrowException;

class ThrowExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext'
        );

        $this->throwException = new ThrowException();
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\Exception\CouldNotFindRouteException
     */
    public function testThrowException()
    {
        $this->throwException->execute($this->builderContext);
    }
}

