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
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

/**
 * This class will make the Route classes using
 * Generic documents using.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class RouteMaker implements RouteMakerInterface
{
    protected $defaults = array();

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function init(array $options)
    {
        $options = array_merge(array(
            'defaults' => array(),
        ), $options);

        $this->defaults = $options['defaults'];
    }

    public function make(RouteStack $routeStack)
    {
        $paths = $routeStack->getFullPaths();

        foreach ($paths as $path) {
            $absPath = '/'.$path;
            $doc = $this->dm->find(null, $absPath);

            if (null === $doc) {
                $doc = new Route;
                $doc->setDefaults($this->defaults);
                $doc->setId($absPath);
            }

            $routeStack->addRoute($doc);
        }
    }
}
