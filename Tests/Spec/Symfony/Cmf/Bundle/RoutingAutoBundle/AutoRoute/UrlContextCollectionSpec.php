<?php

namespace Spec\Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use PhpSpec\ObjectBehavior;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRouteInterface;

class UrlContextCollectionSpec extends ObjectBehavior
{
    public function let(\stdClass $subjectObject)
    {
        $this->beConstructedWith($subjectObject);
    }

    public function it_can_create_a_new_url_context_and_add_it_to_the_stack()
    {
        $locale = 'fr';

        $urlContext = $this->createUrlContext($locale);
        $urlContext->shouldHaveType('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\UrlContext');
        $urlContext->getSubjectObject()->shouldHaveType('stdClass');
    }

    public function it_can_determine_if_a_given_route_is_in_the_stack(
        AutoRouteInterface $autoRoute1,
        AutoRouteInterface $autoRoute2,
        UrlContext $urlContext
    ) {
        $urlContext->getAutoRoute()->willReturn($autoRoute1);
        $this->addUrlContext($urlContext);

        $this->containsAutoRoute($autoRoute1)->shouldReturn(true);
        $this->containsAutoRoute($autoRoute2)->shouldReturn(false);
    }

    public function it_can_get_an_auto_route_by_its_tag(
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

        $urlContext1->getAutoRoute()->willReturn($autoRoute1);
        $urlContext2->getAutoRoute()->willReturn($autoRoute2);
        $urlContext3->getAutoRoute()->willReturn($autoRoute3);

        $this->addUrlContext($urlContext1);
        $this->addUrlContext($urlContext2);
        $this->addUrlContext($urlContext3);

        $this->getAutoRouteByTag('fr')->shouldReturn($autoRoute1);
        $this->getAutoRouteByTag('zf')->shouldReturn(null);
    }
}
