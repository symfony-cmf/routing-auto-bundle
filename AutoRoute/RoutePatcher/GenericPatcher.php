<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RoutePatcher;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RoutePatcherInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\BuilderContext;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Document\Generic;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Document\AutoRoute;

/**
 * This class will make the Route classes using
 * Generic documents using.
 *
 * @todo: Make this use PHPCR\Util\NodeHelper:makePath
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class GenericPatcher implements RoutePatcherInterface
{
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function makeRoutes(BuilderUnitContext $buc)
    {
        $components = $buc->getPathComponents();

        foreach ($components as $i => $component) {

            $path .= '/'.$component;

            $doc = $this->dm->find(null, $path);

            if (null === $doc) {
                $parent = $context->getLastRoute();

                if (null === $parent) {
                    $parent = $this->dm->find(null, '/');
                }

                // otherwise create a generic document
                $doc = new Generic;
                $doc->setNodename($component);
                $doc->setParent($parent);
                    var_dump($parent);
                }
            }

            $context->addRoute($doc);
        }
    }
}
