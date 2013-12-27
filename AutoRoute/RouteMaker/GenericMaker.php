<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMaker;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMakerInterface;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Document\Generic;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

/**
 * This class will make the Route classes using
 * Generic documents using.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class GenericMaker implements RouteMakerInterface
{
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function make(RouteStack $routeStack)
    {
        $paths = $routeStack->getFullPaths();
        $meta = $this->dm->getClassMetadata('Doctrine\ODM\PHPCR\Document\Generic');

        foreach ($paths as $path) {
            $absPath = '/'.$path;
            $doc = $this->dm->find(null, $absPath);

            if (null === $doc) {
                $doc = new Generic;
                $meta->setIdentifierValue($doc, $absPath);
            }

            $routeStack->addRoute($doc);
        }
    }
}
