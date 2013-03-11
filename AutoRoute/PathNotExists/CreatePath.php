<?php

namespace Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathNotExists;

use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\PathActionInterface;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\AutoRoute\BuilderContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Doctrine\ODM\PHPCR\Document\Generic;
use Symfony\Cmf\Bundle\RoutingAutoRouteBundle\Document\AutoRoute;
use Doctrine\ODM\PHPCR\DocumentManager;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class CreatePath implements PathActionInterface
{
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function init(array $options)
    {
    }

    public function execute(BuilderContext $context)
    {
        $paths = $context->getPathStack();
        $fullPath = implode('/', $paths);
        $path = '';

        $components = preg_split('#/#', $fullPath, null, PREG_SPLIT_NO_EMPTY);

        // @todo: this is dumb but safe. We always check the FULL path (i.e. look-up each component). 
        //        An optimization would be to check to see if the route at the given path is already 
        //        in the stack before doing a lookup.
        
        foreach ($components as $i => $component) {
            $path .= '/'.$component;

            $doc = $this->dm->find(null, $path);

            if (null === $doc) {
                $parent = $context->getLastRoute();

                if (null === $parent) {
                    $parent = $this->dm->find(null, '/');
                }

                // @todo: Would be nice to abstract away the class instantiation here.
                if ($context->isLastBuilder() && ($i == count($components) - 1)) {
                    // If this is the last builder and this is the last component
                    // create the actual content route.
                    $doc = new AutoRoute;
                    $doc->setName($component);
                    $doc->setRouteContent($context->getRouteContent());
                    $doc->setParent($parent);
                } else {
                    // otherwise create a generic document
                    $doc = new Generic;
                    $doc->setNodename($component);
                    $doc->setParent($parent);
                }
            }

            $context->addRoute($doc);
        }
    }
}
