<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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

