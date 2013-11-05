<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\AbstractConfigurationTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Configuration;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\CmfRoutingAutoExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class ConfigurationTest extends AbstractConfigurationTestCase
{
    protected $inputConfig;

    public function setUp()
    {
        $this->inputConfig = array(
            'auto_route_mappings' => array(
                'Acme\BasicCmsBundle\Document\Page' => array(
                    'content_path' => array(
                        'route_stacks' => array(
                            'pages' => array(
                                'provider' => array(
                                    'name' => 'specified',
                                    'options' => array(
                                        'path' => '/cms/routes/page', 
                                    ),
                                ),
                                'exists_action' => array(
                                    'strategy' => 'use',
                                    'options' => array(),
                                ),
                                'not_exists_action' => array(
                                    'strategy' => 'create',
                                    'options' => array(),
                                ),
                            ),
                        ),
                    ),
                    'content_name' => array(
                        'provider' => array(
                            'name' => 'content_method',
                            'options' => array(
                                'method' => 'getTitle',
                            ),
                        ),
                        'exists_action' => array(
                            'strategy' => 'auto_increment',
                            'options' => array(
                                'pattern' => '-%d',
                            ),
                        ),
                        'not_exists_action' => array(
                            'strategy' => 'create',
                            'options' => array(),
                        ),
                    ),
                ),
            ),
        );
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
    protected function getExtension()
    {
        return new CmfRoutingAutoExtension();
    }

    public function testYamlConfig()
    {
        $this->assertProcessedConfigurationEquals(
            $this->loadYamlFile(__DIR__.'/../Fixtures/config/valid.yml'),
            $this->inputConfig
        );
    }

    public function testXmlConfig()
    {
        $this->assertProcessedConfigurationEquals(
            $this->loadXmlFile(__DIR__.'/../Fixtures/config/valid.xml'),
            $this->inputConfig
        );
    }

    protected function loadXmlFile($file)
    {
        return $this->doLoadFile($file, 'XmlFileLoader');
    }

    protected function loadYamlFile($file)
    {
        return $this->doLoadFile($file, 'YamlFileLoader');
    }

    protected function doLoadFile($file, $loader)
    {
        $container = new ContainerBuilder();

        $extension = $this->getExtension();
        $container->registerExtension($extension);

        $loader = 'Symfony\Component\DependencyInjection\Loader\\'.$loader;
        $loader = new $loader($container, new FileLocator(dirname($file)));
        $loader->load(basename($file));

        return $container->getExtensionConfig($extension->getAlias());
    }
}
