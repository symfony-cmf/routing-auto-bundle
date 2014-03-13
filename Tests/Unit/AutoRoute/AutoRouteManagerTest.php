<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\OperationStack;

class AutoRouteManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->driver = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Driver\DriverInterface');
        $this->urlGenerator = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlGeneratorInterface');
        $this->defunctRouteHandler = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandlerInterface');
        $this->autoRouteManager = new AutoRouteManager(
            $this->driver,
            $this->urlGenerator,
            $this->defunctRouteHandler
        );
    }

    public function provideBuildOperationStack()
    {
        return array(
            array(
                array(
                    'locales' => array('en', 'fr', 'de', 'be'),
                    'urls' => array(
                        '/en/this-is-an-route' => array('conflict' => false),
                        '/fr/this-is-an-route' => array('conflict' => false),
                        '/de/this-is-an-route' => array('conflict' => false),
                        '/be/this-is-an-route' => array('conflict' => false),
                    ),
                    'existingRoute' => false,
                ),
            ),
        );
    }

    /**
     * @dataProvider provideBuildOperationStack
     */
    public function testBuildOperationStack($params)
    {
        $params = array_merge(array(
            'locales' => array(),
            'urls' => array(),
        ), $params);

        $this->driver->expects($this->once())
            ->method('getLocales')
            ->will($this->returnValue($params['locales']));

        $localesCount = count($params['locales']);
        $urls = $params['urls'];
        $indexedUrls = array_keys($urls);
        $expectedRoutes = array();
        $document = new \stdClass;

        for ($i = 0; $i < $localesCount; $i++) {
            $expectedRoutes[] = $this->getMock('Symfony\Cmf\Component\Routing\RouteObjectInterface');

            $this->urlGenerator->expects($this->exactly($localesCount))
                ->method('generateUrl')
                ->with($document)
                ->will($this->returnCallback(function () use ($i, $indexedUrls) {
                    return $indexedUrls[$i];
                }));

            $this->driver->expects($this->exactly($localesCount))
                ->method('createRoute')
                ->will($this->returnCallback(function ($url, $document) use ($i, $expectedRoutes) {
                    return $expectedRoutes[$i];
                }));
        }

        $operationStack = new OperationStack();
        $this->autoRouteManager->buildOperationStack($operationStack, $document);

        $res = $operationStack->getPersistStack();
        $this->assertEquals($expectedRoutes, $res);
    }
}
