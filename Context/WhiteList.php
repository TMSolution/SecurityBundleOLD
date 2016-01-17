<?php
namespace Core\SecurityBundle\Context;

class WhiteList implements WhiteListInterface
{

    private $routes;

    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
    }

    public function allowRoute($route) {
        return in_array($route, $this->routes);
    }

}