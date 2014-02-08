<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Model;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

/**
 * Sub class of Route to enable automatically generated routes
 * to be identified.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRoute extends Route
{
    protected $locale;

    public function getLocale() 
    {
        return $this->locale;
    }
    
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
    
}
