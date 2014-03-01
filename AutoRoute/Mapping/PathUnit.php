<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\AutoRoute\Mapping;

/**
 * The PathUnit object holds the configuration of one builder unit.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class PathUnit
{
    protected $name;
    protected $provider;
    protected $notExistsAction;
    protected $existsAction;
    protected $builderUnit;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setProvider($name, array $options = array())
    {
        $this->provider = array('name' => $name, 'options' => $options);
    }

    public function setExistsAction($name, array $options = array())
    {
        $this->existsAction = array('name' => $name, 'options' => $options);
    }

    public function setNotExistsAction($name, array $options = array())
    {
        $this->notExistsAction = array('name' => $name, 'options' => $options);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function getExistsAction()
    {
        return $this->existsAction;
    }

    public function getNotExistsAction()
    {
        return $this->notExistsAction;
    }
}
