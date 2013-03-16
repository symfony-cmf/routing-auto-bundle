<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMakerInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoIncrementPath implements PathActionInterface
{
    protected $dm;
    protected $routeMaker;

    public function __construct(DocumentManager $dm, RouteMakerInterface $routeMaker)
    {
        $this->dm = $dm;
        $this->routeMaker = $routeMaker;
    }

    public function init(array $options)
    {
    }

    public function execute(RouteStack $routeStack, BuilderContext $context)
    {
        $inc = 1;

        $path = $context->getStagedPath();

        do {
            $newPath = sprintf('%s-%d', $path, $inc++);
        } while (null !== $this->dm->find(null, $newPath));

        $stack->replaceLastPathElement($newPath);

        $this->routeMaker->makeRoutes($routeStack);
    }
}
