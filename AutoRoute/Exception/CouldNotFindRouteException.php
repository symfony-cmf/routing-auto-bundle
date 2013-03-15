<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class CouldNotFindRouteException extends \Exception
{
    public function __construct($path)
    {
        $message = sprintf('Could not find route component at path "%s".',
            $path
        );

        parent::__construct($message);
    }
}


