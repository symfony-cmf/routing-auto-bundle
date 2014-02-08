<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception;

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
