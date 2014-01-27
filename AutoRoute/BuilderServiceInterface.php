<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Classes used in the build process, usually path provides and actions.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
interface BuilderServiceInterface
{
    /**
     * Configures the possible options.
     *
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolverInterface $resolver);

    /**
     * Gets the options resolver.
     *
     * @return OptionsResolverInterface
     */
    public function getOptionsResolver();
}
