<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\PathProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists\PathProvider;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider\ContentObjectProvider;
use Doctrine\ODM\PHPCR\ReferrersCollection;
use Doctrine\Common\Collections\ArrayCollection;

class ContentObjectProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->metadata = $this->getMockBuilder(
            'Doctrine\ODM\PHPCR\Mapping\ClassMetadata'
        )->disableOriginalConstructor()->getMock();

        $this->phpcrSession = $this->getMock(
            'PHPCR\SessionInterface'
        );

        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );
        $this->routeStack = $this->getMockBuilder(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack'
        )->disableOriginalConstructor()->getMock();

        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->uow = $this->getMockBuilder('Doctrine\ODM\PHPCR\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentObject = new \stdClass;
        $this->route1 = $this->getMock('Symfony\Cmf\Bundle\RoutingBundle\Document\Route');
        $this->object = new ContentObjectTestClass($this->contentObject);


        $this->provider = new ContentObjectProvider($this->dm);
    }

    protected function setupDocumentPersisted($isPersisted)
    {
        $this->dm->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($this->metadata));
        $this->dm->expects($this->once())
            ->method('getPhpcrSession')
            ->will($this->returnValue($this->phpcrSession));
        $this->phpcrSession->expects($this->once())
            ->method('nodeExists')
            ->will($this->returnValue($isPersisted));
    }

    /**
     * @expectedException Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\MissingOptionException
     */
    public function testProvidePath_noMethod()
    {
        $this->provider->init(array());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testProvideMethod_invalidMethod()
    {
        $this->routeStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));
        $this->builderContext->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->object));
        $this->provider->init(array('method' => 'invalidMethod'));
        $this->provider->providePath($this->routeStack);
    }

    protected function setupTest($slugify = true)
    {
        $this->routeStack->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->builderContext));
        $this->builderContext->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($this->object));
        $this->dm->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));
    }

    protected function getReferrersCollection($referrers)
    {
        $refCollection = $this->getMockBuilder('Doctrine\ODM\PHPCR\ReferrersCollection')
            ->disableOriginalConstructor()
            ->getMock();
        $refCollection->expects($this->any())
            ->method('filter')
            ->will($this->returnCallback(function ($callback) use ($referrers) {
                return new ArrayCollection(array_filter($referrers, $callback));
            }));

        return $refCollection;
    }

    public function testProvideObjectWithReferrers()
    {
        $this->setupTest();
        $this->setupDocumentPersisted(true);
        $this->dm->expects($this->once())
            ->method('getReferrers')
            ->will($this->returnValue($this->getReferrersCollection(array(
                $this->route1,
            ))));

        $this->route1->expects($this->once())
            ->method('getRouteContent')
            ->will($this->returnValue($this->contentObject));

        $this->route1->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('/this/is/path'));

        $this->uow->expects($this->once())
            ->method('getScheduledInserts')
            ->will($this->returnValue(array()));

        $this->routeStack->expects($this->once())
            ->method('addPathElements')
            ->with(array('this', 'is', 'path'));

        $this->provider->init(array('method' => 'getObject'));
        $this->provider->providePath($this->routeStack);
    }
}

class ContentObjectTestClass
{
    protected $contentObject;

    public function __construct($contentObject)
    {
        $this->contentObject = $contentObject;
    }

    public function getObject()
    {
        return $this->contentObject;
    }
}
