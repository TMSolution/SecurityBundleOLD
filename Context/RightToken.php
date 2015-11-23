<?php

namespace Core\SecurityBundle\Context;

class RightToken implements RightTokenInterface
{
    private $name;
    
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function isEmpty()
    {
        return $this->name === '';
    }
    
    public function equals(RightTokenInterface $token)
    {
        return $this->name === $token->getName();
    }
    
    public function __toString()
    {
        return $this->getName();
    }
}


