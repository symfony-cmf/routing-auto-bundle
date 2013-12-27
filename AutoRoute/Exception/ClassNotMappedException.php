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

