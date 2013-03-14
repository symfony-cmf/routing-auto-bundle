<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\Exception;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ClassNotMappedException extends \Exception
{
    public function __construct($classFqn)
    {
        $message = sprintf('The class "%s" has not been mapped for auto routing.',
            $classFqn
        );

        parent::__construct($message);
    }
}

