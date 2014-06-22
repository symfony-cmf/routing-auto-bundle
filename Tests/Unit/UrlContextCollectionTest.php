<?php

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit;

use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Component\RoutingAuto\UrlContextCollection;

class UrlContextCollectionTest extends BaseTestCase
{
    protected $urlContextCollection;

    public function setUp()
    {
        parent::setUp();
        $this->subjectObject = new \stdClass;

        for ($i = 1; $i <= 3; $i++) {
            $this->{'autoRoute' . $i} = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
            $this->{'urlContext' . $i} = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UrlContext');
            $this->{'urlContext' . $i}->getAutoRoute()->willReturn($this->{'autoRoute' . $i});
        }

        $this->urlContextCollection = new UrlContextCollection($this->subjectObject);
    }

    public function testGetSubjectObject()
    {
        $this->assertEquals($this->subjectObject, $this->urlContextCollection->getSubjectObject());
    }

    public function testCreateUrlContext()
    {
        $res = $this->urlContextCollection->createUrlContext('fr');
        $this->assertInstanceOf('Symfony\Cmf\Component\RoutingAuto\UrlContext', $res);
        $this->assertEquals('fr', $res->getLocale());
    }

    public function provideContainsAutoRoute()
    {
        return array(
            array(
                array('urlContext1', 'urlContext2', 'urlContext3'),
                'autoRoute1',
                true
            ),
            array(
                array('urlContext2', 'urlContext3'),
                'autoRoute1',
                false
            ),
        );
    }


    /**
     * @dataProvider provideContainsAutoRoute
     */
    public function testContainsAutoRoute($urlContextNames, $targetName, $expected)
    {
        foreach ($urlContextNames as $urlContextName) {
            $this->urlContextCollection->addUrlContext($this->$urlContextName->reveal());
        }

        $res = $this->urlContextCollection->containsAutoRoute($this->$targetName->reveal());

        $this->assertEquals($expected, $res);
    }
}
