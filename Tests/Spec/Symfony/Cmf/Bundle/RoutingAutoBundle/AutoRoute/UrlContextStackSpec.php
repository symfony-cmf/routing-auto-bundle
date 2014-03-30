<?php

namespace Spec\Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRouteInterface;

class UrlContextStackSpec extends ObjectBehavior
{
    function let(\stdClass $subjectObject)
    {
        $this->beConstructedWith($subjectObject);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContextStack');
    }

    function it_can_create_a_new_url_context_and_add_it_to_the_stack()
    {
        $locale = 'fr';

        $urlContext = $this->createUrlContext($locale);
        $urlContext->shouldHaveType('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext');
        $urlContext->getSubjectObject()->shouldHaveType('stdClass');
    }

    function it_has_a_method_which_determines_if_a_given_route_is_in_the_stack(
        AutoRouteInterface $autoRoute1,
        AutoRouteInterface $autoRoute2,
        UrlContext $urlContext
    ) {
        $urlContext->getRoute()->willReturn($autoRoute1);
        $this->pushUrlContext($urlContext);

        $this->containsRoute($autoRoute1)->shouldReturn(true);
        $this->containsRoute($autoRoute2)->shouldReturn(false);
    }

    function it_has_a_method_to_get_an_auto_route_by_its_tag(
        AutoRouteInterface $autoRoute1,
        AutoRouteInterface $autoRoute2,
        AutoRouteInterface $autoRoute3,
        UrlContext $urlContext1,
        UrlContext $urlContext2,
        UrlContext $urlContext3
    ) {
        $autoRoute1->getAutoRouteTag()->willReturn('fr');
        $autoRoute2->getAutoRouteTag()->willReturn('de');
        $autoRoute3->getAutoRouteTag()->willReturn('en');

        $urlContext1->getRoute()->willReturn($autoRoute1);
        $urlContext2->getRoute()->willReturn($autoRoute2);
        $urlContext3->getRoute()->willReturn($autoRoute3);

        $this->pushUrlContext($urlContext1);
        $this->pushUrlContext($urlContext2);
        $this->pushUrlContext($urlContext3);

        $this->getRouteByTag('fr')->shouldReturn($autoRoute1);
        $this->getRouteByTag('zf')->shouldReturn(null);
    }
}
