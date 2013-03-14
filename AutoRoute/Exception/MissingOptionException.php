<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\Exception;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class MissingOptionException extends \Exception
{
    public function __construct($context, $key)
    {
        $message = sprintf('%s expected option %s but did not get it.',
            $context, $key
        );

        parent::__construct($message);
    }
}
