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
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;

class MetadataFactoryTest extends BaseTestCase
{
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->factory = new MetadataFactory();
    }

    public function testStoreAndGetClassMetadata()
    {
        $stdClassMetadata = $this->prophet->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\ClassMetadata');
        $stdClassMetadata->getClassName()->willReturn('stdClass');
        $stdClassMetadata->getExtendedClass()->willReturn(null);
        $classMetadata = $stdClassMetadata->reveal();

        $this->factory->addMetadatas(array($classMetadata));

        $this->assertSame($classMetadata, $this->factory->getMetadataForClass('stdClass'));
    }

    public function testMergingParentClasses()
    {
        $this->markTestSkipped('todo');
        return;
        $childMetadata = new ClassMetadata('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\ChildClass');
        $childMetadata->setUrlSchema('{parent}/{title}');
        $childTokenProvider = $this->createTokenProvider('provider1');
        $childTokenProviderTitle = $this->createTokenProvider('provider2');
        $childMetadata->addTokenProvider('category', $childTokenProvider);
        $childMetadata->addTokenProvider('title', $childTokenProviderTitle);

        $parentMetadata = new ClassMetadata('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\ParentClass');
        $parentMetadata->setUrlSchema('/{category}/{publish_date}');
        $parentTokenProvider = $this->createTokenProvider('provider3');
        $parentTokenProviderDate = $this->createTokenProvider('provider4');
        $parentMetadata->addTokenProvider('category', $parentTokenProvider);
        $parentMetadata->addTokenProvider('publish_date', $parentTokenProviderDate);

        $this->factory->addMetadatas(array($childMetadata, $parentMetadata));

        $resolvedMetadata = $this->factory->getMetadataForClass('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\ChildClass');
        $resolvedProviders = $resolvedMetadata->getTokenProviders();
        $this->assertSame($childTokenProvider, $resolvedProviders['category']);
        $this->assertSame($childTokenProviderTitle, $resolvedProviders['title']);
        $this->assertSame($parentTokenProviderDate, $resolvedProviders['publish_date']);

        $this->assertEquals('/{category}/{publish_date}/{title}', $resolvedMetadata->getUrlSchema());
    }

    public function testMergeExtendedClass()
    {
        $parentMetadata = new ClassMetadata('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\ParentClass');
        $parentMetadata->setUrlSchema('{title}');
        $parentMetadata->setExtendedClass('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\Parent1Class');

        $parent1Metadata = new ClassMetadata('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\Parent1Class');
        $parent1TokenProvider = $this->createTokenProvider('provider1');
        $parent1Metadata->addTokenProvider('title', $parent1TokenProvider);

        $this->factory->addMetadatas(array($parentMetadata, $parent1Metadata));

        $resolvedMetadata = $this->factory->getMetadataForClass('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\ParentClass');
        $resolvedProviders = $resolvedMetadata->getTokenProviders();
        $this->assertSame($parent1TokenProvider, $resolvedProviders['title']);
        $this->assertEquals('{title}', $resolvedMetadata->getUrlSchema());
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailsWithCircularReference()
    {
        $parentMetadata = new ClassMetadata('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\ParentClass');
        $parentMetadata->setUrlSchema('{title}');
        $parentMetadata->setExtendedClass('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\Parent1Class');

        $parent1Metadata = new ClassMetadata('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\Parent1Class');
        $parent1Metadata->setExtendedClass('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\ParentClass');
        $parent1TokenProvider = $this->createTokenProvider('provider1');
        $parent1Metadata->addTokenProvider('title', $parent1TokenProvider);

        $this->factory->addMetadatas(array($parentMetadata, $parent1Metadata));

        $resolvedMetadata = $this->factory->getMetadataForClass('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Fixtures\ParentClass');
    }

    protected function createTokenProvider($name)
    {
        return array('name' => $name);
    }
}
