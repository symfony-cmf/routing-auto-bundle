<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\DependencyInjection;

use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Configuration;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\CmfRoutingAutoExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;

class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    protected function getContainerExtension()
    {
        return new CmfRoutingAutoExtension();
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testSupportsAllConfigFormats()
    {
        $expectedConfiguration = array(
            'auto_mapping' => false,
            'mapping' => array(
                'resources' => array(
                    array('path' => 'Resources/config/SpecificObject.yml', 'type' => null),
                    array('path' => 'Document/Post.php', 'type' => 'annotation'),
                    array('path' => 'Resources/config/foo.xml', 'type' => null),
                ),
            ),
            'persistence' => array(
                'phpcr' => array(
                    'enabled' => true,
                    'route_basepath' => '/routes',
                ),
            ),
        );

        $sources = array_map(function ($path) {
            return __DIR__.'/../../Resources/Fixtures/'.$path;
        }, array(
            'config/config.yml',
            'config/config.xml',
            'config/config.php',
        ));

        foreach ($sources as $source) {
            $this->assertProcessedConfigurationEquals($expectedConfiguration, array($source));
        }
    }
}
