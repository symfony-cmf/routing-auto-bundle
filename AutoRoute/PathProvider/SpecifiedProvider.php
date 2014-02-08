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

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Exception\MissingOptionException;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class SpecifiedProvider extends AbstractPathProvider
{
    protected $path;

    public function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('path'));

        $resolver->setNormalizers(array(
            'path' => function (Options $options, $value) {
                if ('/' === substr($value, 0, 1)) {
                    $value = substr($value, 1);
                }

                return $value;
            },
        ));
    }

    public function providePath(RouteStack $routeStack, array $options)
    {
        $routeStack->addPathElements(explode('/', $options['path']));
    }
}
