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

    public function prophesize($classOrInterface = null)
    {
        return $this->prophet->prophesize($classOrInterface);
    }
 
    protected function assertPostConditions()
    {
        $this->prophet->checkPredictions();
    }

    protected function tearDown()
    {
        $this->prophet = null;
    }

    protected function onNotSuccessfulTest(\Exception $e)
    {
        if ($e instanceof PredictionException) {
            $e = new \PHPUnit_Framework_AssertionFailedError($e->getMessage(), $e->getCode(), $e);
        }

        return parent::onNotSuccessfulTest($e);
    }
}
