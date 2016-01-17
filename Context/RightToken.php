<?php

namespace Core\SecurityBundle\Context;

class RightToken implements RightTokenInterface
{
    private $name;
    private $whiteListed;
    
    public function __construct($name, $whiteListed = false)
    {
        $this->name = $name;
        $this->whiteListed = $whiteListed;
    }

    public function getName()
    {
        return $this->name;
    }

    public function equals(RightTokenInterface $token)
    {
        return $token instanceof RightToken && $this->getName() === $token->getName();
    }

    public function isWhiteListed()
    {
        return $this->whiteListed;
    }

    public function __toString()
    {
        return $this->getName();
    }

}


