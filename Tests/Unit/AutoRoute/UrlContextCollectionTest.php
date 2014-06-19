<?php

namespace Unit\AutoRoute;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContextCollection;

class UrlContextCollectionTest extends BaseTestCase
{
    protected $urlContextCollection;

    public function setUp()
    {
        parent::setUp();
        $this->subjectObject = new \stdClass;

        for ($i = 1; $i <= 3; $i++) {
            $this->{'autoRoute' . $i} = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRouteInterface');
            $this->{'urlContext' . $i} = $this->prophesize('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext');
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
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext', $res);
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
