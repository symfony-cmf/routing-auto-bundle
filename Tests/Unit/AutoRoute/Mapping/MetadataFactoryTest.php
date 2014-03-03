<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\Mapping;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\ClassMetadata;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\MetadataFactory;

class MetadataFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;

    public function setUp()
    {
        $this->factory = new MetadataFactory();
    }

    public function testStoreAndGetClassMetadata()
    {
        $stdClassMetadata = $this
            ->getMockBuilder('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $stdClassMetadata->expects($this->any())->method('getClassName')->will($this->returnValue('stdClass'));

        $this->factory->addMetadatas(array($stdClassMetadata));

        $this->assertSame($stdClassMetadata, $this->factory->getMetadataForClass('stdClass'));
    }

    public function testMergingParentClasses()
    {
        $childMetadata = new ClassMetadata('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\ChildClass');
        $childMetadata->setUrlSchema('$schema/%title%');
        $childTokenProvider = $this->createTokenProvider('category');
        $childTokenProviderTitle = $this->createTokenProvider('title');
        $childMetadata->addTokenProvider($childTokenProvider);
        $childMetadata->addTokenProvider($childTokenProviderTitle);

        $parentMetadata = new ClassMetadata('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\ParentClass');
        $parentMetadata->setUrlSchema('/%category%/%publish_date%');
        $parentTokenProvider = $this->createTokenProvider('category');
        $parentTokenProviderDate = $this->createTokenProvider('publish_date');
        $parentMetadata->addTokenProvider($parentTokenProvider);
        $parentMetadata->addTokenProvider($parentTokenProviderDate);

        $this->factory->addMetadatas(array($childMetadata, $parentMetadata));

        $resolvedMetadata = $this->factory->getMetadataForClass('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\ChildClass');
        $resolvedProviders = $resolvedMetadata->getTokenProviders();
        $this->assertSame($childTokenProvider, $resolvedProviders['category']);
        $this->assertSame($childTokenProviderTitle, $resolvedProviders['title']);
        $this->assertSame($parentTokenProviderDate, $resolvedProviders['publish_date']);

        $this->assertEquals('/%category%/%publish_date%/%title%', $resolvedMetadata->getUrlSchema());
    }

    protected function createTokenProvider($name)
    {
        $tokenProvider = $this
            ->getMockBuilder('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\TokenProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $tokenProvider->expects($this->any())->method('getName')->will($this->returnValue($name));

        return $tokenProvider;
    }
}
