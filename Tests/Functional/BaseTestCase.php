<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase as TestingBaseTestCase;

class BaseTestCase extends TestingBaseTestCase
{
    private $repository;

    protected function setUp()
    {
        parent::setUp();

        $container = $this->getContainer();
        switch (self::$kernel->getEnvironment()) {
            case 'doctrine_phpcr_odm':
                $this->repository = new Repository\DoctrinePhpcrOdm($container);
                break;
            case 'doctrine_orm':
                $this->repository = new Repository\DoctrineOrm($container);
                break;
            default:
                throw new \RuntimeException(sprintf(
                    'Could not find (phpunit functional test) repository for env: "%s"',
                    self::$kernel->getEnvironment()
                ));
        }

        $this->repository->init();
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function getObjectManager()
    {
        return $this->repository->getObjectManager();
    }

    public function getApplication()
    {
        $application = new Application(self::$kernel);

        return $application;
    }
}
