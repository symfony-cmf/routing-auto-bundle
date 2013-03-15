<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AutoRouteManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;

class AutoRouteManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->phpcrSession = $this->getMock('PHPCR\SessionInterface');
        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dm->expects($this->once())
            ->method('getPhpcrSession')
            ->will($this->returnValue($this->phpcrSession));

        $this->slugifier = $this->getMock('Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface');
        $this->mapping = array(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute\TestDocument' => array(
                'base_path' => null,
                'route_method_name' => 'getRouteName',
                'base_path_auto_create' => false
            )
        );

        $this->document = new TestDocument;
        $this->parentRoute = new \stdClass;

        $this->odmMetadata = new ClassMetadata(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Routing\TestDocument'
        );

        $this->autoRouteManager = new AutoRouteManager(
            $this->dm,
            $this->mapping,
            $this->slugifier,
            '/default/path'
        );

        $this->testRoute1 = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute');
        $this->testRoute2 = $this->getMock('Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute');
    }

    protected function bootstrapRouteMetadata()
    {
        $this->dm->expects($this->once())
            ->method('getClassMetadata')
            ->with('Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute\TestDocument')
            ->will($this->returnValue($this->odmMetadata));
    }

    protected function bootstrapExistingDocument($isExisting)
    {
        $this->phpcrSession->expects($this->once())
            ->method('nodeExists')
            ->will($this->returnValue($isExisting));
    }

    protected function bootstrapRouteName()
    {
        $this->slugifier->expects($this->once())
            ->method('slugify')
            ->with('test route')
            ->will($this->returnValue('test-route'));
    }

    protected function bootstrapParentRoute()
    {
        // getParentRoute
        $this->dm->expects($this->once())
            ->method('find')
            ->with(null, '/default/path')
            ->will($this->returnValue($this->parentRoute));
    }

    public function testUpdateRouteForDocument_notPersisted()
    {
        $this->bootstrapRouteMetadata();
        $this->bootstrapExistingDocument(false);
        $this->bootstrapRouteName();
        $this->bootstrapParentRoute();

        $autoRoute = $this->autoRouteManager->updateAutoRouteForDocument($this->document);

        $this->assertEquals('test-route', $autoRoute->getName());
        $this->assertSame($this->parentRoute, $autoRoute->getParent());
    }

    public function testUpdateRouteForDocument_withAutoRouteAndOtherReferrer()
    {
        $this->bootstrapRouteMetadata();
        $this->bootstrapExistingDocument(true);
        $this->bootstrapRouteName();
        $this->bootstrapParentRoute();

        $this->dm->expects($this->once())
            ->method('getReferrers')
            ->will($this->returnValue(new ArrayCollection(array(
                new \stdClass,
                $this->testRoute1,
            ))));

        $autoRoute = $this->autoRouteManager->updateAutoRouteForDocument($this->document);

        $this->assertSame($this->testRoute1, $autoRoute);
    }

    /**
     * @expectedException \Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\MoreThanOneAutoRoute
     */
    public function testUpdateRouteForDocument_withMoreThanOneAutoRoute()
    {
        $this->bootstrapRouteMetadata();
        $this->bootstrapExistingDocument(true);

        $this->dm->expects($this->once())
            ->method('getReferrers')
            ->will($this->returnValue(new ArrayCollection(array(
                $this->testRoute1,
                $this->testRoute2,
            ))));

        $this->autoRouteManager->updateAutoRouteForDocument($this->document);
    }
}

class TestDocument
{
    public function getRouteName()
    {
        return 'test route';
    }
}

