<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\DependencyInjection;

use Symfony\Cmf\Component\Testing\Unit\ConfigurationTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Configuration;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\CmfRoutingAutoExtension;

class ConfigurationTest extends ConfigurationTestCase
{
    protected function getExpectedResult()
    {
        return array(
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

    protected function getFilenames()
    {
        return array(
            'yaml' => __DIR__.'/../Fixtures/config/valid.yml',
            'xml'  => __DIR__.'/../Fixtures/config/valid.xml',
        );
    }
}
