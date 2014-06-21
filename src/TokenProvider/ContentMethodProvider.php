<?php

namespace Symfony\Cmf\Component\RoutingAuto\AutoRoute\TokenProvider;

use Symfony\Cmf\Component\RoutingAuto\AutoRoute\TokenProviderInterface;
use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Cmf\Component\RoutingAuto\AutoRoute\UrlContext;

class ContentMethodProvider implements TokenProviderInterface
{
    protected $slugifier;

    public function __construct(SlugifierInterface $slugifier)
    {
        $this->slugifier = $slugifier;
    }

    protected function checkMethodExists($object, $method)
    {
        if (!method_exists($object, $method)) {
            throw new \InvalidArgumentException(sprintf(
                'Method "%s" does not exist on object "%s"',
                $method,
                get_class($object)
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function provideValue(UrlContext $urlContext, $options)
    {
        $object = $urlContext->getSubjectObject();
        $method = $options['method'];

        $this->checkMethodExists($object, $method);

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
