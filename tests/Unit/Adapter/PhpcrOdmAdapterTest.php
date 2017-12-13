<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Adapter;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use PHPCR\NodeInterface;
use PHPCR\SessionInterface;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Adapter\PhpcrOdmAdapter;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;

class PhpcrOdmAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentManager|ObjectProphecy
     */
    private $dm;

    /**
     * @var ClassMetadataFactory|ObjectProphecy
     */
    private $metadataFactory;

    /**
     * @var ClassMetadata|ObjectProphecy
     */
    private $metadata;

    private $contentDocument;

    private $contentDocument2;

    private $baseNode;

    private $parentRoute;

    /**
     * @var AutoRouteInterface|ObjectProphecy
     */
    private $route;

    /**
     * @var UriContext|ObjectProphecy
     */
    private $uriContext;

    /**
     * @var SessionInterface|ObjectProphecy
     */
    private $phpcrSession;

    /**
     * @var NodeInterface|ObjectProphecy
     */
    private $phpcrRootNode;

    private $baseRoutePath;

    /**
     * @var PhpcrOdmAdapter
     */
    private $adapter;

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
        return [
            [true, ['fr', 'de']],
            [false],
        ];
    }

    /**
     * @dataProvider provideGetLocales
     */
    public function testGetLocales($isTranslateable, $locales = [])
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
        return [
            ['stdClass', 'some/path', 'fr'],
        ];
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
        return [
            ['/foo/bar', '/test/foo', 'bar', true],
        ];
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
        $this->uriContext->getDefaults()->willReturn([]);
        $this->uriContext->getSubject()->willReturn($this->contentDocument);
        $res = $this->adapter->createAutoRoute($this->uriContext->reveal(), 'fr');
        $this->assertNotNull($res);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $res);
        $this->assertEquals($expectedName, $res->getName());

        $this->assertSame($this->parentRoute, $res->getParentDocument());
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
        $this->uriContext->getDefaults()->willReturn([
            'one' => 'k1',
            'two' => 'k2',
        ]);
        $this->uriContext->getSubject()->willReturn($this->contentDocument);

        $res = $this->adapter->createAutoRoute($this->uriContext->reveal(), 'fr');
        $this->assertNotNull($res);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $res);
        $this->assertEquals('to', $res->getName());
        $this->assertEquals([
            '_auto_route_tag' => 'fr',
            'type' => 'cmf_routing_auto.primary',
            'one' => 'k1',
            'two' => 'k2',
        ], $res->getDefaults());

        $this->assertSame($this->parentRoute, $res->getParentDocument());
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
        $this->uriContext->getSubject()->willReturn($this->contentDocument);
        $this->adapter->createAutoRoute($this->uriContext->reveal(), 'it');
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
        $this->uriContext->getSubject()->willReturn($this->contentDocument);
        $this->adapter->createAutoRoute($this->uriContext->reveal(), 'fr');
    }

    public function testGetRealClassName()
    {
        $res = $this->adapter->getRealClassName('Class/Foo');
        $this->assertEquals('Class/Foo', $res);
    }

    public function provideCompareRouteContent()
    {
        return [
            [true],
            [false],
        ];
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

    public function provideCompareAutoRouteLocale()
    {
        return [
            'a not localized route and a null locale' => [
                PhpcrOdmAdapter::TAG_NO_MULTILANG,
                null,
                true,
            ],
            'a not localized route and a locale' => [
                PhpcrOdmAdapter::TAG_NO_MULTILANG,
                'en',
                false,
            ],
            'a localized route and the matching locale' => [
                'en',
                'en',
                true,
            ],
            'a localized route and a not matching locale' => [
                'en',
                'fr',
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideCompareAutoRouteLocale
     */
    public function testCompareAutoRouteLocale($autoRouteLocale, $locale, $shouldMatch)
    {
        $this->route->getLocale()->willReturn($autoRouteLocale);

        $areMatching = $this->adapter->compareAutoRouteLocale($this->route->reveal(), $locale);

        $this->assertSame($shouldMatch, $areMatching);
    }

    public function testGetReferringRoutes()
    {
        $this->dm->getReferrers($this->contentDocument, null, null, null, 'Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface')
            ->willReturn([$this->route]);
        $res = $this->adapter->getReferringAutoRoutes($this->contentDocument);

        $this->assertSame([$this->route->reveal()], $res);
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
