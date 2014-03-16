<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Unit;

use Prophecy\Prophet;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    protected $prophet;

    public function setUp()
    {
        $this->prophet = new Prophet();
    }

    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }
}
