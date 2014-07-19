<?php

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
