<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

/**
 * Simple redirecting controller for AutoRouteInterface documents.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class RedirectController
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param AutoRouteInterface $routeDocument
     */
    public function redirectAction(AutoRouteInterface $routeDocument)
    {
        $routeTarget = $routeDocument->getRedirectTarget();
        $url = $this->router->generate($routeTarget);

        return new RedirectResponse($url, 302);
    }
}
