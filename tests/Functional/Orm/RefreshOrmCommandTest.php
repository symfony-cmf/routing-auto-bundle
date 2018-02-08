<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Orm\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Command\RefreshCommand;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Command\RefreshOrmCommand;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Doctrine\Orm\AutoRoute;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Fixtures\App\Entity\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Orm\OrmBaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Orm\Repository\DoctrineOrm;
use Symfony\Cmf\Component\Testing\Functional\DbManager\ORM;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * @group orm
 *
 * @author WAM Team <develop@wearemarketing.com>
 */
class RefreshOrmCommandTest extends OrmBaseTestCase
{
    public function getKernelConfiguration()
    {
        return [
            'environment' => 'orm',
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        /** @var ORM $dbManager */
        $dbManager = $this->getDbManager('ORM');
        $dbManager->purgeDatabase();
    }

    public function testCommand()
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlog(true);

        $this->updateBlogTitle($repository);

        $application = $this->getApplication();
        $input = new ArrayInput([
            '--verbose' => true,
        ]);
        $output = new NullOutput();
//        $output = new StreamOutput(fopen('php://stdout', 'w'));
        $command = new RefreshCommand();
        $command->setApplication($application);
        $command->run($input, $output);

        $post = $this->getRepository()->findPost('This is a post title');
        $this->getObjectManager()->refresh($post);

        $routes = $post->getRoutes();
        $this->assertCount(2, $routes);
        /** @var AutoRoute $route */
        $route = $routes[1];
        $this->assertEquals('/blog/foobar/2013/03/21/this-is-a-post-title', $route->getStaticPrefix());
    }

    /**
     * @param $repository
     */
    private function updateBlogTitle($repository)
    {
        /** @var Blog $blog */
        $blog = $repository->findBlog('Unit testing blog');
        // test update
        $blog->setTitle('Foobar');
        $blog->mergeNewTranslations();

        /** @var ObjectManager $objectManager */
        $objectManager = $this->getObjectManager();
        $objectManager->persist($blog);
        $objectManager->flush();
        $objectManager->clear();
    }
}
