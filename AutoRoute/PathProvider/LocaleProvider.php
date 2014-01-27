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

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;

/**
 * Provides a ISO-639-1 locale path element based
 * on the locale in the context.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class LocaleProvider extends AbstractPathProvider
{
    public function providePath(RouteStack $routeStack, array $options)
    {
        $context = $routeStack->getContext();
        $locale = $context->getLocale();

        if (!$locale) {
            throw new \RuntimeException(sprintf(
                'LocaleProvider requires that a locale is set on the BuilderContext for "%s"',
                get_class($context->getContent())
            ));
        }

        $routeStack->addPathElements(array($locale));
    }
}
