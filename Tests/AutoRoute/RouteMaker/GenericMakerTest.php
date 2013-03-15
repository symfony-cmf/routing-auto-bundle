<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\AutoRoute\RouteMaker;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker\GenericMaker;

class GenericMakerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->builderContext = $this->getMock(
            'Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext'
        );

        $this->genericMaker = new GenericMaker($this->dm);
        $this->doc2 = new \stdClass;
    }

    public function dataProviderGenericMaker()
    {
        $doc1 = $this->getMock('Doctrine\ODM\PHPCR\Document\Generic');
        $doc1->expects($this->once())->method('getNodename')->will($this->returnValue('test1'));

        return array(
            array(
                array('/test1/test2', '/test3/test4'),
                array(
                    '/test1' => null,
                    '/' => new \stdClass,
                    '/test1/test2' => null,
                    '/test1/test2/test3' => null,
                    '/test1/test2/test3/test4' => null,
                ),
                array(
                    'test1', 'test2', 'test3', 'test4'
                )
            ),
            array(
                array('/test1'),
                array(
                    '/test1' => $doc1,
                ),
                array(
                    'test1'
                )
            ),
        );
    }

    /**
     * @dataProvider dataProviderGenericMaker
     */
    public function testGenericMaker($pathStack, $dmDocs, $expectedAdditions)
    {
        $nbNotFounds = 0;
        foreach ($dmDocs as $dmDoc => $val) {
            $nbNotFounds += null === $val ? 1 : 0;
        }

        $this->builderContext->expects($this->once())
            ->method('getPathStack')
            ->will($this->returnValue($pathStack));

        $finds = array();
        $this->dm->expects($this->any())
            ->method('find')
            ->will($this->returnCallback(function ($class, $id) use ($dmDocs, &$finds) {
                $finds[] = $id;
                return $dmDocs[$id];
            }));

        $addedRoutes = array();
        $this->builderContext->expects($this->any())
            ->method('addRoute')
            ->will($this->returnCallback(function ($route) use (&$addedRoutes) {
                $addedRoutes[$route->getNodename()] = $route;
            }));

        $this->builderContext->expects($this->exactly($nbNotFounds))
            ->method('getLastRoute')
            ->will($this->returnCallback(function () use (&$addedRoutes) {
                return end($addedRoutes) ? : null;
            }));

        $this->genericMaker->makeRoutes($this->builderContext);

        $this->assertEquals(array_keys($dmDocs), $finds);
        $this->assertEquals(array_keys($addedRoutes), $expectedAdditions);
    }
}

