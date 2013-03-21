<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RoutePatcher;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RoutePatcherInterface;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Document\Generic;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

/**
 * This class will make the Route classes using
 * Generic documents using.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class GenericPatcher implements RoutePatcherInterface
{
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function patch(RouteStack $routeStack)
    {
        $paths = $routeStack->getFullPaths();

        foreach ($paths as $path) {
            $absPath = '/'.$path;
            $doc = $this->dm->find(null, $absPath);

            if (null === $doc) {
                $doc = new Generic;
                $meta = $this->dm->getClassMetadata(get_class($doc));
                $meta->setIdentifierValue($doc, $absPath);
            }

            $routeStack->addRoute($doc);
        }
    }
}
