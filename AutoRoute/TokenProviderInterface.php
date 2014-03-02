<?php

namespace AutoRoute;

interface TokenProviderInterface
{
    /**
     * Return a token value for the given configuration and
     * document.
     *
     * @param array $options
     * @param object $document
     *
     * @return string
     */
    public function getValue($options, $document);

    /**
     * Configure the options for this token provider
     *
     * @param OptionsResolverInterface $optionsResolver
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver);
}
