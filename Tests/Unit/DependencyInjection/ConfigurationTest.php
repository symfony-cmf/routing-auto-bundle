<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\AbstractConfigurationTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Configuration;

class ConfigurationTest extends AbstractConfigurationTestCase
{
    protected $inputConfig;

    public function setUp()
    {
        $this->inputConfig = array(
            'auto_route_mapping' => array(
                'Acme\BasicCmsBundle\Document\Page' => array(
                    'content_path' => array(
                        'pages' => array(
                            'provider' => array(
                                'name' => 'specified',
                                'path' => '/cms/routes/page', 
                            ),
                            'exists_action' => array(
                                'strategy' => 'use',
                            ),
                            'not_exists_action' => array(
                                'strategy' => 'create',
                            ),
                        ),
                    ),
                    'content_name' => array(
                        'provider' => array(
                            'name' => 'content_method',
                            'method' => 'getTitle',
                        ),
                        'exists_action' => array(
                            'strategy' => 'auto_increment',
                            'pattern' => '-%d',
                        ),
                        'not_exists_action' => array(
                            'strategy' => 'create',
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

    public function testYamlConfig()
    {
        $this->assertProcessedConfigurationEquals(
            array(
                $this->inputConfig,
            ),
            $this->inputConfig
        );
    }

    public function testXmlConfig()
    {
        $this->assertProcessedConfigurationEquals(
            array(
                array(
                    'auto-route-mapping' => array(
                        array(
                            'class' => 'Acme\BasicCmsBundle\Document\Page',
                            'content-path' => array(
                                array(
                                    'name' => 'pages',
                                    'provider' => array(
                                        'option' => array(
                                            array(
                                                'name' => 'name',
                                                'value' => 'specified',
                                            ),
                                            array(
                                                'name' => 'path',
                                                'value' => '/cms/routes/page',
                                            ),
                                        ),
                                    ),
                                    'exists-action' => array(
                                        'option' => array(
                                            array(
                                                'name' => 'strategy',
                                                'value' => 'use',
                                            )
                                        ),
                                    ),
                                    'not-exists-action' => array(
                                        'option' => array(
                                            array(
                                                'name' => 'strategy',
                                                'value' => 'create',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'content-name' => array(
                                'provider' => array(
                                    'option' => array(
                                        array(
                                            'name' => 'name',
                                            'value' => 'content_method',
                                        ),
                                        array(
                                            'name' => 'method',
                                            'value' => 'getTitle',
                                        ),
                                    ),
                                ),
                                'exists-action' => array(
                                    'option' => array(
                                        array(
                                            'name' => 'strategy',
                                            'value' => 'auto_increment',
                                        ),
                                        array(
                                            'name' => 'pattern',
                                            'value' => '-%d',
                                        ),
                                    ),
                                ),
                                'not-exists-action' => array(
                                    'option' => array(
                                        array(
                                            'name' => 'strategy',
                                            'value' => 'create',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            $this->inputConfig
        );
    }
}
