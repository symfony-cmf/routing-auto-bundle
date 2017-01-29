<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Model;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;

/**
 * Sub class of Route to enable automatically generated routes
 * to be identified.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRoute extends Route implements AutoRouteInterface
{
    const DEFAULT_KEY_AUTO_ROUTE_LOCALE = '_auto_route_tag';

    /**
     * @var AutoRouteInterface
     */
    protected $redirectRoute;

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->setDefault(self::DEFAULT_KEY_AUTO_ROUTE_LOCALE, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->getDefault(self::DEFAULT_KEY_AUTO_ROUTE_LOCALE);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->setDefault('type', $type);
    }

    /**
     * {@inheritdoc}
     */
    public function setRedirectTarget($redirectRoute)
    {
        $this->redirectRoute = $redirectRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectTarget()
    {
        return $this->redirectRoute;
    }
}
