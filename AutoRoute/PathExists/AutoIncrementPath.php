<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathExists;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathActionInterface;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathNotExists\CreatePath;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoIncrementPath extends CreatePath
{
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function execute(BuilderContext $context)
    {
        $inc = 1;

        $path = $context->getLastPath();

        do {
            $newPath = sprintf('%s-%d', $path, $inc++);
        } while (null !== $this->dm->find(null, $newPath));

        $context->replaceLastPath($newPath);

        parent::execute($context);
    }
}

