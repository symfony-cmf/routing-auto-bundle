<?php

$container->loadFromExtension('cmf_routing_auto', array(
    'auto_mapping' => false,
    'mapping' => array(
        'paths' => array(
            'Resources/config/SpecificObject.yml',
            'Resources/config/foo.xml',
        ),
    ),
));
