<?php

$container->loadFromExtension('cmf_routing_auto', array(
    'mappings' => array(
        'Acme\BasicCmsBundle\Document\Page' => array(
            'content_path' => array(
                'pages' => array(
                    'provider' => array('specified', array('path' => '/cms/routes/page')),
                    'exists_action' => 'use',
                    'not_exists_action' => array(
                        'strategy' => 'create',
                    ),
                ),
            ),
            'content_name' => array(
                'provider' => array('content_method', array('method' => 'getTitle')),
                'exists_action' => array(
                    'strategy' => 'auto_increment',
                    'options' => array(
                        'pattern' => '-%d',
                    ),
                ),
                'not_exists_action' => array('create'),
            ),
        ),
    ),
));
