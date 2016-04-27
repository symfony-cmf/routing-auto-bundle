<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\EventListener;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Adapter\PhpcrOdmAdapter;

abstract class ListenerTestCase extends BaseTestCase
{
    /**
     * It should persist the blog document and create an auto route.
     * It should set the defaults on the route.
     */
    public function testPersistBlog()
    {
        $this->getRepository()->createBlog();

        $autoRoute = $this->getRepository()->findAutoRoute('unit-testing-blog');

        $this->assertNotNull($autoRoute);

        // make sure auto-route has been persisted
        $blog = $this->getRepository()->findBlog('test-blog');
        $routes = $this->getRepository()->findRoutesForBlog($blog);

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $routes[0]);
        $this->assertEquals('unit-testing-blog', $routes[0]->getName());
        $this->assertEquals(PhpcrOdmAdapter::TAG_NO_MULTILANG, $routes[0]->getAutoRouteTag());
        $this->assertEquals(array(
            '_auto_route_tag' => 'no-multilang',
            'type' => 'cmf_routing_auto.primary',
            '_controller' => 'BlogController',
        ), $routes[0]->getDefaults());
    }
}
