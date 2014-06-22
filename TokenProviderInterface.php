<?php

namespace Symfony\Cmf\Component\RoutingAuto;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

interface TokenProviderInterface
{
    /**
     * Return a token value for the given configuration and
     * document.
     *
     * @param object $document
     * @param array  $options
     *
     * @return string
     */
    public function provideValue(UrlContext $urlContext, $options);

    /**
     * Configure the options for this token provider
     *
     * @param OptionsResolverInterface $optionsResolver
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver);
}
