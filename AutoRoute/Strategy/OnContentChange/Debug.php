<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Strategy\OnContentChange;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Strategy\AutoRouteChangedInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Strategy\BuilderContextActionInterface;

class Debug implements BuilderContextActionInterface
{
    public $options;
    public $executed = false;

    public function init(array $options)
    {
        $this->options = $options;
    }

    public function execute(BuilderContext $context)
    {
        $this->executed = true;
    }
}

