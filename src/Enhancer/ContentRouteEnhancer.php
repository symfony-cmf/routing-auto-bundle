<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Enhancer;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Adapter\OrmAdapter;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Doctrine\Orm\AutoRoute;
use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * This enhancer injects resolves content data.
 *
 * @author WAM Team <develop@wearemarketing.com>
 */
class ContentRouteEnhancer implements RouteEnhancerInterface
{
    /**
     * @var OrmAdapter
     */
    private $manager;

    public function __construct(OrmAdapter $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param array   $defaults
     * @param Request $request
     *
     * @return array
     */
    public function enhance(array $defaults, Request $request)
    {
        $routeObject = $defaults[RouteObjectInterface::ROUTE_OBJECT];

        if ($routeObject instanceof AutoRoute) {
            $this->manager->resolveRouteContent($routeObject);
        }

        return $defaults;
    }
}
