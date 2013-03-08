<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\Exception;

class MoreThanOneAutoRoute extends \Exception
{
    public function __construct($document)
    {
        $message = sprintf(
            'Found more than one AutoRoute for document of class "%s"',
            get_class($document)
        );
        parent::__construct($message);
    }

}

