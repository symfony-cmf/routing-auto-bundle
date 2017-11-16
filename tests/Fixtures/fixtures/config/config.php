<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$container->loadFromExtension('cmf_routing_auto', [
    'auto_mapping' => false,
    'mapping' => [
        'resources' => [
            'Resources/config/SpecificObject.yml',
            ['path' => 'Document/Post.php', 'type' => 'annotation'],
            ['path' => 'Resources/config/foo.xml'],
        ],
    ],
    'persistence' => [
        'phpcr' => [
            'route_basepath' => '/routes',
        ],
    ],
]);
