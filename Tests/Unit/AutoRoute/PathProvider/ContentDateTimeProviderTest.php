<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\PathProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists\PathProvider;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider\ContentDateTimeProvider;

class ContentDateTimeProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );
        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();
        $this->slugifier = $this->getMock(
            'Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface'
        );

        $this->provider = new ContentDateTimeProvider($this->slugifier);
        $this->object = new ContentDateTimeTestClass();
    }


    /**
     * @expectedException \BadMethodCallException
     */
    public function testProvideDateTime_invalidDateTime()
    {
        $this->routeStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));
        $this->builderContext->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->object));

        $this->provider->providePath($this->routeStack, array(
            'method' => 'invalidDateTime',
            'date_format' => 'Y-m-d',
            'slugify' => true,
        ));
    }

    public function setupTest($slugify = true)
    {
        $this->routeStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));
        $this->builderContext->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->object));

        if ($slugify) {
            $this->slugifier->expects($this->any())
                ->method('slugify')
                ->will($this->returnCallback(function ($el) { return $el; }));
        }
    }

    public function testProvideDateTime()
    {
        $this->setupTest();
        $this->routeStack->expects($this->once())
            ->method('addPathElements')
            ->with(array('2013', '03', '21'));

        $this->provider->providePath($this->routeStack, array(
            'method' => 'getDate', 
            'date_format' => 'Y/m/d',
            'slugify' => true,
        ));
    }
}

class ContentDateTimeTestClass
{
    public function getDate()
    {
        return new \DateTime('2013/03/21');
    }

    public function getBadDate()
    {
        return "thisisastring";
    }
}
