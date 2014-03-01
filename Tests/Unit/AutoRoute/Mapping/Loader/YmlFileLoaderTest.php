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
    protected $loader;

    public function setUp()
    {
        $this->loader  = new YmlFileLoader();
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
        $this->assertNull($this->loader->load($this->getFixturesPath('empty.yml')));
    }

    /**
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider getFailsOnInvalidConfigFilesData
     */
    public function testFailsOnInvalidConfigFiles($file)
    {
        $this->loader->load($this->getFixturesPath($file));
    }

    public function getFailsOnInvalidConfigFilesData()
    {
        $files = array(
            'invalid1.yml',
            'invalid2.yml',
            'invalid3.yml',
            'invalid4.yml',
            'invalid5.yml',
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
        $result = $this->loader->load($this->getFixturesPath($file));

        $this->assertContainsOnlyInstancesOf('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping\MappingData', $result);
        $check($result);
    }

    public function getCorrectlyParsesValidConfigFilesData()
    {
        $test = $this;
        $serviceConfig = function ($name, $options = array()) {
            return array('name' => $name, 'options' => $options);
        };

        return array(
            array('valid1.yml', function ($mappings) use ($test) {
                $test->assertCount(1, $mappings);
                $mapping = $mappings[0];
                $test->assertEquals('stdClass', $mapping->getClassName());
                $test->assertEquals('/cmf/blog', $mapping->getUrlSchema());
                $test->assertCount(0, $mapping->getPathUnits());
            }),
            array('valid2.yml', function ($mappings) use ($test, $serviceConfig) {
                $test->assertCount(1, $mappings);
                $mapping = $mappings[0];
                $test->assertEquals('stdClass', $mapping->getClassName());
                $test->assertEquals('/forum/%category%/%post_name%', $mapping->getUrlSchema());

                $test->assertCount(2, $mapping->getPathUnits());
                $units = $mapping->getPathUnits();

                $test->assertEquals('category', $units['category']->getName());
                $test->assertEquals($serviceConfig('method', array('method' => 'getCategoryName')), $units['category']->getProvider());
                $test->assertEquals($serviceConfig('use'), $units['category']->getExistsAction());
                $test->assertEquals($serviceConfig('throw'), $units['category']->getNotExistsAction());

                $test->assertEquals('post_name', $units['post_name']->getName());
                $test->assertEquals($serviceConfig('method', array('method' => 'getName')), $units['post_name']->getProvider());
                $test->assertEquals($serviceConfig('auto_increment', array('format' => '-%d')), $units['post_name']->getExistsAction());
                $test->assertEquals($serviceConfig('create'), $units['post_name']->getNotExistsAction());
            }),
            array('valid3.yml', function ($mappings) use ($test) {
                $test->assertCount(2, $mappings);
                $test->assertEquals('stdClass', $mappings[0]->getClassName());
                $test->assertEquals('/forum/%category%/%post_name%', $mappings[0]->getUrlSchema());

                $test->assertEquals('Symfony\Cmf\Bundle\RoutingAutoBundle\CmfRoutingAutoBundle', $mappings[1]->getClassName());
                $test->assertEquals('/forum/%category%', $mappings[1]->getUrlSchema());
            }),
        );
    }

    protected function getFixturesPath($fixture)
    {
        return __DIR__.'/../../../../Resources/Fixtures/loader_config/'.$fixture;
    }
}
