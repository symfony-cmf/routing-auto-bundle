<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathProviderInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\MissingOptionException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\CouldNotFindRouteException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Provides a ISO-639-1 locale path element based
 * on the locale in the context.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class LocaleProvider implements PathProviderInterface
{
    public function init(array $options)
    {
    }

    public function providePath(RouteStack $routeStack)
    {
        $context = $routeStack->getContext();
        $locale = $context->getLocale();

        if (!$locale) {
            throw new \RuntimeException(
                'LocaleProvider requires that a locale is set on the BuilderContext'
            );
        }

        $routeStack->addPathElements(array($locale));
    }
}
