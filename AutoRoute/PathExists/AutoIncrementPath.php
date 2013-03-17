<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoIncrementPath implements PathActionInterface
{
    protected $dm;
    protected $routeMaker;

    public function __construct(DocumentManager $dm, RouteMaker $routeMaker)
    {
        $this->dm = $dm;
        $this->routeMaker = $routeMaker;
    }

    public function init(array $options)
    {
    }

    public function execute(RouteStack $routeStack)
    {
        $inc = 1;

        $path = $routeStack->getFullPath();

        do {
            $newPath = sprintf('%s-%d', $path, $inc++);
        } while (null !== $this->dm->find(null, $newPath));

        $routeStack->replaceLastPathElement(basename($newPath));

        $this->routeMaker->makeRoutes($routeStack);
    }
}
