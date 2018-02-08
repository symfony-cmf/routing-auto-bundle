<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Doctrine\Orm;

use Symfony\Cmf\Bundle\RoutingBundle\Model\Route;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;

/**
 * TODO: create 2 tables or only one.
 * TODO: if we create one table, which name for all routes and auto routes.
 * TODO: Add all index as in Orm\Route.
 * TODO: Think about how to modify orm model of routing bundle.
 *
 * @author WAM Team <develop@wearemarketing.com>
 */
class AutoRoute extends Route implements AutoRouteInterface
{
    const CONTENT_CLASS_KEY = 'contentClass';

    const CONTENT_ID_KEY = 'contentId';

    const DEFAULT_KEY_AUTO_ROUTE_LOCALE = '_route_auto_tag';

    /**
     * Identifier.
     *
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var string
     */
    protected $canonicalName;

    /**
     * @var string
     */
    private $contentClass;

    /**
     * @var array
     */
    private $contentId;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getPath();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the sort order of this route.
     *
     * @param int $position
     *
     * @return self
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get the sort order of this route.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Getter for CanonicalName.
     *
     * @return string
     */
    public function getCanonicalName()
    {
        return $this->canonicalName;
    }

    /**
     * Setter for CanonicalName.
     *
     * @param string $canonicalName
     *
     * @return $this
     */
    public function setCanonicalName($canonicalName)
    {
        $this->canonicalName = $canonicalName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRedirectTarget($autoTarget)
    {
        $this->setDefault('_controller', 'FrameworkBundle:Redirect:redirect');
        $this->setDefault('route', $autoTarget->getName());
        $this->setDefault('permanent', true);
        $this->setDefault('ignoreAttributes', ['type']);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectTarget()
    {
        return $this->getDefault('route');
    }

    /**
     * Getter for content class.
     *
     * @return string
     */
    public function getContentClass()
    {
        return $this->contentClass;
    }

    /**
     * Setter for content class.
     *
     * @param string $class
     *
     * @return $this
     */
    public function setContentClass($class)
    {
        $this->contentClass = $class;

        return $this;
    }

    /**
     * Getter for content class.
     *
     * @return mixed
     */
    public function getContentId()
    {
        return $this->contentId;
    }

    /**
     * Setter for content id.
     *
     * @param mixed $id
     *
     * @return $this
     */
    public function setContentId(array $id)
    {
        $this->contentId = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->getDefault(self::DEFAULT_KEY_AUTO_ROUTE_LOCALE);
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->setDefault(self::DEFAULT_KEY_AUTO_ROUTE_LOCALE, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($mode)
    {
        $this->setDefault('type', $mode);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getDefault('type');
    }
}
