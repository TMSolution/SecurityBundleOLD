<?php

namespace Core\SecurityBundle\Context;

class NoRightToken implements RightTokenInterface
{
    private $name;

    public function __construct($name = '')
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isWhiteListed()
    {
        return false;
    }

    public function equals(RightTokenInterface $token)
    {
        return $token instanceof NoRightToken && $this->getName() === $token->getName();
    }
    
    public function __toString()
    {
        return '';
    }
}
