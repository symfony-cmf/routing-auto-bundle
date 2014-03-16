<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProvider;

use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\TokenProviderInterface;
use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContentMethodProvider implements TokenProviderInterface
{
    protected $slugifier;

    public function __construct(SlugifierInterface $slugifier)
    {
        $this->slugifier = $slugifier;
    }

    /**
     * {@inheritDoc}
     */
    public function provideValue($object, $options)
    {
        $method = $options['method'];

        if (!method_exists($object, $method)) {
            throw new \InvalidArgumentException(sprintf(
                'Method "%s" does not exist on object "%s"',
                $method,
                get_class($object)
            ));
        }

        $value = $object->$method();

        if ($options['slugify']) {
            $value = $this->slugifier->slugify($value);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver)
    {
        $optionsResolver->setRequired(array(
            'method',
        ));

        $optionsResolver->setDefaults(array(
            'slugify' => true,
        ));

        $optionsResolver->setAllowedTypes(array(
            'slugify' => 'bool',
        ));
    }
}
