<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContextCollection;

class AutoRouteManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->driver = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\AdapterInterface');
        $this->urlGenerator = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlGeneratorInterface');
        $this->defunctRouteHandler = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\DefunctRouteHandlerInterface');
        $this->autoRouteManager = new AutoRouteManager(
            $this->driver,
            $this->urlGenerator,
            $this->defunctRouteHandler
        );
    }

    public function provideBuildUrlContextCollection()
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
     * @dataProvider provideBuildUrlContextCollection
     */
    public function testBuildUrlContextCollection($params)
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
            $expectedRoutes[] = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRouteInterface');

            $this->urlGenerator->expects($this->exactly($localesCount))
                ->method('generateUrl')
                ->will($this->returnCallback(function () use ($i, $indexedUrls) {
                    return $indexedUrls[$i];
                }));
        }

        $this->driver->expects($this->exactly($localesCount))
            ->method('createAutoRoute')
            ->will($this->returnCallback(function ($url, $document) use ($expectedRoutes) {
                static $i = 0;
                return $expectedRoutes[$i++];
            }));

        $urlContextCollection = new UrlContextCollection($document);
        $this->autoRouteManager->buildUrlContextCollection($urlContextCollection);

        foreach ($expectedRoutes as $expectedRoute) {
            $this->assertTrue($urlContextCollection->containsAutoRoute($expectedRoute), 'URL context collection contains route: ' . spl_object_hash($expectedRoute));
        }
    }
}
