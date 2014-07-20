<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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
