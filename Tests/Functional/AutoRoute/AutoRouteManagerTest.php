<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\Functional\AutoRoute;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Tests\Functional\BaseTestCase;

/**
 * Description here
 *
 * @author Daniel Leech <daniel@dantleech.com>
 * @date 13/03/08
 */
class AutoRouteManagerTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->arm = $this->getContainer()->get(
            'symfony_cmf_routing_auto_route.auto_route_manager'
        );
    }

    public function testContainer()
    {
        $res = $this->arm->getDefaultPath();
        $this->assertEquals('/cms/auto-routes', $res);

        $refl = new \ReflectionClass(get_class($this->arm));
        $prop = $refl->getProperty('mapping');
        $prop->setAccessible(true);
        $res = $prop->getValue($this->arm);

        $this->assertEquals(array(
            'Symfony\Cmf\Bundle\AutoRouteBundle\Tests\Functional\app\Document\Post' => array(
                'base_path' => '/test/posts/test-post',
                'route_method_name' => 'getTitle'
            )
        ), $res);
    }
}
