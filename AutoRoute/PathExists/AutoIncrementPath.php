<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists;

use PHPCR\Util\PathHelper;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface;
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
    protected $format = '%s-%d';

    public function __construct(DocumentManager $dm, RouteMakerInterface $routeMaker)
    {
        $this->dm = $dm;
        $this->routeMaker = $routeMaker;
    }

    public function init(array $options)
    {
        if (isset($options['format'])) {
            $this->format = '%s'.$options['format'];
        }
    }

    public function execute(RouteStack $routeStack)
    {
        $inc = 1;

        $path = $routeStack->getFullPath();

        $route = $this->dm->find(null, $path);
        $context = $routeStack->getContext();

        if ($route->getContent() === $context->getContent()) {
            $routeStack->addRoute($route);

            return;
        }

        do {
            $newPath = sprintf($this->format, $path, $inc++);
        } while (null !== $this->dm->find(null, $newPath));

        $routeStack->replaceLastPathElement(PathHelper::getNodeName($newPath));
        $this->routeMaker->make($routeStack);
    }
}
