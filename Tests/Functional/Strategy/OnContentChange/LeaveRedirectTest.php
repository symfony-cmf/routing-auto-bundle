<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Strategy\OnContentChange;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Page;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute;

class LeaveRedirectTest extends BaseTestCase
{
    protected function createPage($name)
    {
        $parent = $this->getDm()->find(null, '/test');

        $page = new Page;
        $page->parent = $parent;
        $page->name = $name;
        $page->body = 'Body for page ' . $name;

        $this->getDm()->persist($page);
        $this->getDm()->flush();
        $this->getDm()->clear();

        return $page;
    }

    public function testLeaveRedirect()
    {
        $factory = $this->getContainer()->get('cmf_routing_auto.factory');

        $factory->mergeMapping('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Page', array(
            'on_content_change' => array(
                'actions' => array(
                    'action' => array(
                        'action' => 'leave_redirect',
                    ),
                ),
            ),
        ));

        $this->createPage('Page 1');

        $page = $this->getDm()->find(null, '/test/Page 1');
        $page->name = 'Page 5';
        $this->getDm()->persist($page);
        $this->getDm()->flush();
        $this->getDm()->clear();

        $originalRouteDoc = $this->getDm()->find(null, '/test/auto-route/page-1');
        $newRoutedoc = $this->getDm()->find(null, '/test/auto-route/page-5');

        $this->assertNotNull($originalRouteDoc);
        $this->assertNotNull($newRouteDoc);

        $this->assertInstanceOf(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRedirectRoute',
            $originalRouteDoc
        );

        $this->assertInstanceOf(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute',
            $originalRouteDoc
        );

        $this->assertEquals($newRouteDoc, $originalRouteDoc->getRouteTarget());
    }
}
