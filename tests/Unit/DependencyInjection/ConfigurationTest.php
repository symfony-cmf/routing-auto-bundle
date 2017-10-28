<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\CmfRoutingAutoExtension;
use Symfony\Cmf\Bundle\RoutingAutoBundle\DependencyInjection\Configuration;

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
        $expectedConfiguration = [
            'auto_mapping' => false,
            'mapping' => [
                'resources' => [
                    ['path' => 'Resources/config/SpecificObject.yml', 'type' => null],
                    ['path' => 'Document/Post.php', 'type' => 'annotation'],
                    ['path' => 'Resources/config/foo.xml', 'type' => null],
                ],
            ],
            'persistence' => [
                'phpcr' => [
                    'enabled' => true,
                    'route_basepath' => '/routes',
                ],
            ],
        ];

        $sources = array_map(function ($path) {
            return __DIR__.'/../../Fixtures/fixtures/'.$path;
        }, [
            'config/config.yml',
            'config/config.xml',
            'config/config.php',
        ]);

        foreach ($sources as $source) {
            $this->assertProcessedConfigurationEquals($expectedConfiguration, [$source]);
        }
    }
}
