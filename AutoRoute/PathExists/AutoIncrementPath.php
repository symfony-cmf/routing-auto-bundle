<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\PathExists;

use PHPCR\Util\PathHelper;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\AbstractPathAction;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteStack;
use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\RouteMakerInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoIncrementPath extends AbstractPathAction
{
    protected $dm;
    protected $routeMaker;

    public function __construct(DocumentManager $dm, RouteMakerInterface $routeMaker)
    {
        $this->dm = $dm;
        $this->routeMaker = $routeMaker;
    }

    public function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'format' => '%s-%d',
        ));

        $resolver->setNormalizers(array(
            'format' => function (Options $options, $value) {
                if ('%s' !== substr($value, 0, 2)) {
                    $value = '%s'.$value;
                }

                return $value;
            },
        ));
    }

    public function execute(RouteStack $routeStack, array $options)
    {
        $inc = 1;

        $path = $routeStack->getFullPath();

        $route = $this->dm->find(null, $path);
        $context = $routeStack->getContext();

        if ($route->getContent() === $context->getContent()) {
            $routeStack->addRoute($route);

            return;
        }

        do {
            $newPath = sprintf($options['format'], $path, $inc++);
        } while (null !== $this->dm->find(null, $newPath));

        $routeStack->replaceLastPathElement(PathHelper::getNodeName($newPath));
        $this->routeMaker->make($routeStack);
    }
}
