<?php

namespace Core\SecurityBundle\Context;

interface WhiteListInterface
{
    /**
     * @param $route route name
     * @return bool true if route is white listed, otherwise false
     */
    public function allowRoute($route);
}
