<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\AutoRoute\Adapter;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\PhpcrOdmAdapter;

class AutoRouteManagerTest extends BaseTestCase
{
    protected $dm;

    public function setUp()
    {
        parent::setUp();

        $this->dm = $this->prophet->prophesize('Doctrine\ODM\PHPCR\DocumentManager');
        $this->metadataFactory = $this->prophet->prophesize('Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory');
        $this->metadata = $this->prophet->prophesize('Doctrine\ODM\PHPCR\Mapping\ClassMetadata');
        $this->contentDocument = new \stdClass;
        $this->contentDocument2 = new \stdClass;
        $this->parentRoute = new \stdClass;
        $this->route = $this->prophet->prophesize('Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route');

        $this->phpcrSession = $this->prophet->prophesize('PHPCR\SessionInterface');
        $this->phpcrRootNode = $this->prophet->prophesize('PHPCR\NodeInterface');
        $this->baseRoutePath = '/test';

        $this->adapter = new PhpcrOdmAdapter($this->dm->reveal(), $this->baseRoutePath);
    }

    public function provideGetLocales()
    {
        return array(
            array(true, array('fr', 'de')),
            array(false),
        );
    }

    /**
     * @dataProvider provideGetLocales
     */
    public function testGetLocales($isTranslateable, $locales = array())
    {
        $this->dm->isDocumentTranslatable($this->contentDocument)
            ->willReturn($isTranslateable);

        if ($isTranslateable) {
            $this->dm->getLocalesFor($this->contentDocument)
                ->willReturn($locales);
        }

        $res = $this->adapter->getLocales($this->contentDocument);
        $this->assertEquals($locales, $res);
    }

    public function provideTranslatedObject()
    {
        return array(
            array('stdClass', 'some/path', 'fr'),
        );
    }

    /**
     * @dataProvider provideTranslatedObject
     */
    public function testTranslateObject($className, $id, $locale)
    {
        $this->dm->getMetadataFactory()
            ->willReturn($this->metadataFactory->reveal());
        $this->metadataFactory->getMetadataFor($className)
            ->willReturn($this->metadata->reveal());
        $this->metadata->getName()
            ->willReturn($className);
        $this->metadata->getIdentifierValue($this->contentDocument)
            ->willReturn($id);

        $this->dm->findTranslation($className, $id, $locale)
            ->willReturn($this->contentDocument);

        $res = $this->adapter->translateObject($this->contentDocument, $locale);
        $this->assertSame($this->contentDocument, $res);
    }

    public function provideCreateRoute()
    {
        return array(
            array('/foo/bar', '/foo', 'bar', true)
        );
    }

    /**
     * @dataProvider provideCreateRoute
     */
    public function testCreateRoute($path, $expectedParentPath, $expectedName, $parentPathExists)
    {
        $this->dm->getPhpcrSession()->willReturn($this->phpcrSession);
        $this->phpcrSession->getRootNode()->willReturn($this->phpcrRootNode);
        if ($parentPathExists) {
            $this->dm->find(null, $expectedParentPath)
                ->willReturn($this->parentRoute);
        } else {
            $this->dm->find(null, $expectedParentPath)
                ->willReturn(null);
        }

        $res = $this->adapter->createRoute($path, $this->contentDocument);
        $this->assertNotNull($res);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $res);
        $this->assertEquals($expectedName, $res->getName());
        $this->assertSame($this->parentRoute, $res->getParent());
        $this->assertSame($this->contentDocument, $res->getContent());
    }

    public function testGetRealClassName()
    {
        $res = $this->adapter->getRealClassName('Class/Foo');
        $this->assertEquals('Class/Foo', $res);
    }

    public function provideCompareRouteContent()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider provideCompareRouteContent
     */
    public function testCompareRouteContent($isMatch)
    {
        $this->route->getContent()->willReturn($this->contentDocument);
        $content = $isMatch ? $this->contentDocument : $this->contentDocument2;

        $this->adapter->compareRouteContent($this->route->reveal(), $this->contentDocument);
    }

    public function testGetReferringRoutes()
    {
        $this->dm->getReferrers($this->contentDocument, null, null, null, 'Symfony\Cmf\Component\Routing\RouteObjectInterface')
            ->willReturn(array($this->route));
        $res = $this->adapter->getReferringRoutes($this->contentDocument);

        $this->assertSame(array($this->route->reveal()), $res);
    }

    public function testFindRouteForUrl()
    {
        $url = '/this/is/url';
        $expectedRoutes = array($this->route->reveal());

        $this->dm->find(null, $this->baseRoutePath . $url)->willReturn($expectedRoutes);

        $res = $this->adapter->findRouteForUrl($url);
        $this->assertSame($expectedRoutes, $res);
    }
}
