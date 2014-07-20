<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Controller;

use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TestController extends Controller
{
    public function redirectAction(AutoRouteInterface $routeDocument)
    {
        $routeTarget = $routeDocument->getRedirectTarget();
        return $this->redirect($this->get('router')->generate($routeTarget));
    }
}
