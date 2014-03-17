<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\Mapping\Loader;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\Loader\MetadataDriver;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;
use Prophecy\Argument;

class MetadataDriverTest extends BaseTestCase
{
    protected $loader;
    protected $driver;

    public function setUp()
    {
        parent::setUp();

        $this->loader = $this->prophet->prophesize('Symfony\Component\Config\Loader\LoaderInterface');
        $this->driver = new MetadataDriver($this->loader->reveal(), array(array('path' => 'some_resource.yml')));
    }

    public function testSingletonLoading()
    {
        $metadata = $this->prophet->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\ClassMetadata');
        $metadata->getClassName()->willReturn('stdClass');

        $this->loader->load('some_resource.yml', null)->shouldBeCalledTimes(1)->willReturn(array($metadata->reveal()));

        $this->driver->loadMetadataForClass(new \ReflectionClass('stdClass'));
        $this->driver->loadMetadataForClass(new \ReflectionClass('stdClass'));
    }

    public function testClassMerging()
    {
        $metadata = $this->prophet->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\ClassMetadata');
        $metadata->getClassName()->willReturn('stdClass');
        $metadata->merge(Argument::type('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\ClassMetadata'))->shouldBeCalled();

        $metadata1 = $this->prophet->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\ClassMetadata');
        $metadata1->getClassName()->willReturn('stdClass');

        $this->loader->load('some_resource.yml', null)->willReturn(array(
            $metadata->reveal(),
            $metadata1->reveal()
        ));

        $this->driver->loadMetadataForClass(new \ReflectionClass('stdClass'));
    }

    public function testDoesNothingIfClassHasNoMetadata()
    {
        $this->loader->load('some_resource.yml', null)->willReturn(array());

        $this->assertNull($this->driver->loadMetadataForClass(new \ReflectionClass('stdClass')));
    }
}
