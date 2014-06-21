<?php

namespace Symfony\Cmf\Component\RoutingAuto\AutoRoute\TokenProvider;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Cmf\Component\RoutingAuto\AutoRoute\UrlContext;

class ContentDateTimeProvider extends ContentMethodProvider
{
    /**
     * {@inheritDoc}
     */
    public function provideValue(UrlContext $urlContext, $options)
    {
        $object = $urlContext->getSubjectObject();
        $method = $options['method'];
        $this->checkMethodExists($object, $method);

        $date = $object->$method();

        if (!$date instanceof \DateTime) {
            throw new \RuntimeException(sprintf('Method %s:%s must return an instance of DateTime.',
                get_class($object),
                $method
            ));
        }

        $string = $date->format($options['date_format']);

        return $string;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setRequired(array(
            'date_format',
        ));

        $optionsResolver->setDefaults(array(
            'date_format' => 'Y-m-d',
        ));

        $optionsResolver->setAllowedTypes(array(
            'date_format' => 'string',
        ));
    }
}
