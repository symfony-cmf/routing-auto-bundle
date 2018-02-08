<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Adapter;

/**
 * Class RefreshOrmCommand.
 *
 * @author WAM Team <develop@wearemarketing.com>
 */
interface AutoRouteRefreshCommandAdapterInterface
{
    /**
     * Return all content of given class.
     *
     * @return array
     */
    public function getAllContent(string $classFqn);

    /**
     * Return the identifier of an auto route.
     *
     * @param $autoRouteableContent
     *
     * @return mixed
     */
    public function getIdentifier($autoRouteableContent);
}
