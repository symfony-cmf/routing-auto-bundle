<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Adapter;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Adapter\PhpcrOdmAdapter;

class PhpcrOdmAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $dm;
    protected $baseRoutePath;

    public function setUp()
    {
        $this->dm = $this->prophesize('Doctrine\ODM\PHPCR\DocumentManager');
        $this->metadataFactory = $this->prophesize('Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory');
        $this->metadata = $this->prophesize('Doctrine\ODM\PHPCR\Mapping\ClassMetadata');
        $this->contentDocument = new \stdClass();
        $this->contentDocument2 = new \stdClass();
        $this->baseNode = new \stdClass();
        $this->parentRoute = new \stdClass();
        $this->route = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->uriContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');

        $this->phpcrSession = $this->prophesize('PHPCR\SessionInterface');
        $this->phpcrRootNode = $this->prophesize('PHPCR\NodeInterface');
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
            array('/foo/bar', '/test/foo', 'bar', true),
        );
    }

    /**
     * @dataProvider provideCreateRoute
     */
    public function testCreateAutoRoute($path, $expectedParentPath, $expectedName, $parentPathExists)
    {
        $this->dm->getPhpcrSession()->willReturn($this->phpcrSession);
        $this->phpcrSession->getRootNode()->willReturn($this->phpcrRootNode);
        $this->dm->find(null, $this->baseRoutePath)->willReturn($this->baseNode);

        if ($parentPathExists) {
            $this->dm->find(null, $expectedParentPath)
                ->willReturn($this->parentRoute);
            $this->dm->find(null, $expectedParentPath.'/'.$expectedName)
                ->willReturn(null);
        } else {
            $this->dm->find(null, $expectedParentPath)
                ->willReturn(null);
        }

        $this->uriContext->getUri()->willReturn($path);
        $this->uriContext->getDefaults()->willReturn(array());
        $res = $this->adapter->createAutoRoute($this->uriContext->reveal(), $this->contentDocument, 'fr');
        $this->assertNotNull($res);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $res);
        $this->assertEquals($expectedName, $res->getName());

        $this->assertSame($this->parentRoute, $res->getParent());
        $this->assertSame($this->contentDocument, $res->getContent());
    }

    /**
     * It should set the route defaults on the head document.
     */
    public function testCreateAutoRouteSetDefaults()
    {
        $this->dm->getPhpcrSession()->willReturn($this->phpcrSession);
        $this->phpcrSession->getRootNode()->willReturn($this->phpcrRootNode);
        $this->dm->find(null, $this->baseRoutePath)->willReturn($this->baseNode);

        $this->dm->find(null, '/test/uri')
            ->willReturn($this->parentRoute);
        $this->dm->find(null, '/test/uri/to')
            ->willReturn(null);

        $this->uriContext->getUri()->willReturn('/uri/to');
        $this->uriContext->getDefaults()->willReturn(array(
            'one' => 'k1',
            'two' => 'k2',
        ));

        $res = $this->adapter->createAutoRoute($this->uriContext->reveal(), $this->contentDocument, 'fr');
        $this->assertNotNull($res);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $res);
        $this->assertEquals('to', $res->getName());
        $this->assertEquals(array(
            '_auto_route_tag' => 'fr',
            'type' => 'cmf_routing_auto.primary',
            'one' => 'k1',
            'two' => 'k2',
        ), $res->getDefaults());

        $this->assertSame($this->parentRoute, $res->getParent());
        $this->assertSame($this->contentDocument, $res->getContent());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Failed to migrate existing.*? at "\/test\/generic" .*? It is an instance of "stdClass"\./
     */
    public function testCreateAutoRouteThrowsExceptionIfItCannotMigrateExistingGenericDocumentToAutoRoute()
    {
        $uri = '/generic';
        $genericDocument = $this->prophesize('Doctrine\ODM\PHPCR\Document\Generic');
        $genericDocument->getNode()->willReturn($this->prophesize('PHPCR\NodeInterface')->reveal());
        $genericDocument->getId()->willReturn($this->baseRoutePath.$uri);
        $documentClassMapper = $this->prophesize('Doctrine\ODM\PHPCR\DocumentClassMapperInterface');
        $configuration = $this->prophesize('Doctrine\ODM\PHPCR\Configuration');
        $configuration->getDocumentClassMapper()->willReturn($documentClassMapper->reveal());
        $this->dm->getConfiguration()->willReturn($configuration->reveal());
        $this->dm->getPhpcrSession()->willReturn($this->phpcrSession);
        $this->dm->detach($genericDocument)->willReturn(null);
        $this->dm->find(null, $this->baseRoutePath)->willReturn($this->baseNode);
        $this->dm->find(null, $this->baseRoutePath.$uri)->willReturn(
            $genericDocument->reveal(),
            new \stdClass()
        );
        $this->uriContext->getUri()->willReturn($uri);
        $this->adapter->createAutoRoute($this->uriContext->reveal(), $this->contentDocument, 'it');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage configuration points to a non-existant path
     */
    public function testCreateAutoRouteNonExistingBasePath()
    {
        $this->dm->getPhpcrSession()->willReturn($this->phpcrSession);
        $this->dm->find(null, $this->baseRoutePath)->willReturn(null);
        $this->uriContext->getUri()->willReturn('/asdasd');
        $this->adapter->createAutoRoute($this->uriContext->reveal(), $this->contentDocument, 'fr');
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
        $isMatch ? $this->contentDocument : $this->contentDocument2;

        $this->adapter->compareAutoRouteContent($this->route->reveal(), $this->contentDocument);
    }

    public function testGetReferringRoutes()
    {
        $this->dm->getReferrers($this->contentDocument, null, null, null, 'Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface')
            ->willReturn(array($this->route));
        $res = $this->adapter->getReferringAutoRoutes($this->contentDocument);

        $this->assertSame(array($this->route->reveal()), $res);
    }

    public function testFindRouteForUri()
    {
        $uri = '/this/is/uri';
        $expectedRoute = $this->route->reveal();

        $this->dm->find('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface', $this->baseRoutePath.$uri)->willReturn($expectedRoute);

        $res = $this->adapter->findRouteForUri($uri, $this->uriContext->reveal());
        $this->assertSame($expectedRoute, $res);
    }
}
