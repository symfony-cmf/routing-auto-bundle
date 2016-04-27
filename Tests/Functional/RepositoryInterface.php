<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Document\Blog;

interface RepositoryInterface
{
    public function createBlog();

    public function getObjectManager();

    public function findBlog($blogName);

    public function findRoutesForBlog($blog);

    public function findAutoRoute($url);
}
