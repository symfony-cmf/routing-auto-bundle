<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathActionInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Doctrine\ODM\PHPCR\DocumentManager;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class UsePath implements PathActionInterface
{
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function init(array $options)
    {
    }

    public function execute(RouteStack $routeStack)
    {
        $path = $routeStack->getFullPath();
        $route = $this->dm->find(null, $path);

        if (!$route) {
            throw new \RuntimeException(sprintf(
                'Expected to find a document at "%s",  but didn\'t. This shouldn\'t
                happen. Maybe we have a race condition?',
                $path
            ));
        }

        $routeStack->addRoute($route);
    }
}
