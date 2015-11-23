<?php

namespace Core\SecurityBundle\Context;

class NoRightToken implements RightTokenInterface
{
    public function getName()
    {
        return '';
    }
    
    public function isEmpty()
    {
        return true;
    }
    
    public function equals(RightTokenInterface $token)
    {
        return '' === $token->getName();
    }
    
    public function __toString()
    {
        return '';
    }
}
