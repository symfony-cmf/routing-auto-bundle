<?php

namespace Spec\Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\PHPCR\DocumentManager;

class PhpcrOdmAdapterSpec extends ObjectBehavior
{
    function let(DocumentManager $documentManager)
    {
        $this->beConstructedWith($documentManager, '/tests/foo');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Adapter\PhpcrOdmAdapter');
    }
}
