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

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\Loader\YmlFileLoader;

class YmlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $locator;
    protected $loader;

    public function setUp()
    {
        $this->locator = $this->getMock('Symfony\Component\Config\FileLocatorInterface');
        $this->loader  = new YmlFileLoader($this->locator);
    }

    /**
     * @dataProvider getSupportsData
     */
    public function testSupports($file, $type = null, $support = true)
    {
        $result = $this->loader->supports($file, $type);

        if ($support) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    public function getSupportsData()
    {
        return array(
            array('foo.yml'),
            array('foo.xml', null, false),
            array('foo.yml', 'yaml'),
            array('foo.yml', 'xml', false),
        );
    }

    public function testDoesNothingIfFileIsEmpty()
    {
        $this->locator->expects($this->any())
            ->method('locate')->with('empty.yml')
            ->will($this->returnValue($this->getFixturesPath('empty.yml')));

        $this->assertNull($this->loader->load('empty.yml'));
    }

    /**
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider getFailsOnInvalidConfigFilesData
     */
    public function testFailsOnInvalidConfigFiles($file)
    {
        $this->locator->expects($this->any())
            ->method('locate')->with($file)
            ->will($this->returnValue($this->getFixturesPath($file)));

        $this->loader->load($file);
    }

    public function getFailsOnInvalidConfigFilesData()
    {
        $files = array(
            'invalid1.yml',
            'invalid2.yml',
            'invalid3.yml',
            'invalid4.yml',
        );

        return array_map(function ($file) {
            return array($file);
        }, $files);
    }

    /**
     * @dataProvider getCorrectlyParsesValidConfigFilesData
     */
    public function testCorrectlyParsesValidConfigFiles($file, $check)
    {
        $this->locator->expects($this->any())
            ->method('locate')->with($file)
            ->will($this->returnValue($this->getFixturesPath($file)));

        $result = $this->loader->load($file);

        $this->assertContainsOnlyInstancesOf('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\ClassMetadata', $result);
        $check($result);
    }

    public function getCorrectlyParsesValidConfigFilesData()
    {
        $test = $this;
        $serviceConfig = function ($name, $options = array()) {
            return array('name' => $name, 'options' => $options);
        };

        return array(
            array('valid1.yml', function ($metadatas) use ($test) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];
                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/cmf/blog', $metadata->getUrlSchema());
                $test->assertCount(0, $metadata->getTokenProviders());
            }),
            array('valid2.yml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];
                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/forum/%category%/%post_name%', $metadata->getUrlSchema());

                $test->assertCount(2, $metadata->getTokenProviders());
                $units = $metadata->getTokenProviders();

                $test->assertArrayHasKey('category', $units);
                $test->assertEquals($serviceConfig('method', array('method' => 'getCategoryName')), $units['category']);

                $test->assertArrayHasKey('post_name', $units);
                $test->assertEquals($serviceConfig('method', array('method' => 'getName')), $units['post_name']);
            }),
            array('valid3.yml', function ($metadatas) use ($test) {
                $test->assertCount(2, $metadatas);
                $test->assertEquals('stdClass', $metadatas[0]->getClassName());
                $test->assertEquals('/forum/%category%/%post_name%', $metadatas[0]->getUrlSchema());

                $test->assertEquals('Symfony\Cmf\Bundle\RoutingAutoBundle\CmfRoutingAutoBundle', $metadatas[1]->getClassName());
                $test->assertEquals('/forum/%category%', $metadatas[1]->getUrlSchema());
                $test->assertEquals('stdClass', $metadatas[1]->getExtendedClass());
            }),
            array('valid4.yml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];

                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/cmf/blog', $metadata->getUrlSchema());
                $test->assertEquals($serviceConfig('auto_increment'), $metadata->getConflictResolver());
            }),
            array('valid5.yml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];

                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/blog/%category%/%slug%', $metadata->getUrlSchema());
                $test->assertEquals($serviceConfig('auto_increment', array('token' => 'category')), $metadata->getConflictResolver());

                $test->assertCount(2, $metadata->getTokenProviders());
                $providers = $metadata->getTokenProviders();
                $test->assertArrayHasKey('category', $providers);
                $test->assertEquals($serviceConfig('method', array('method' => 'getCategoryName')), $providers['category']);
                $test->assertArrayHasKey('slug', $providers);
                $test->assertEquals($serviceConfig('property', array('property' => 'title', 'slugify' => true)), $providers['slug']);
            }),
        );
    }

    protected function getFixturesPath($fixture)
    {
        return __DIR__.'/../../../../Resources/Fixtures/loader_config/'.$fixture;
    }
}
