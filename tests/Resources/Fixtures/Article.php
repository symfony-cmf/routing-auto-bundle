<?php

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures;

class Article
{
    public $path;
    public $routes;
    public $title;
    public $locale;
    public $date;

    public function getTitle()
    {
        return $this->title;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }
}
