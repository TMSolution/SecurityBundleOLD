<?php

namespace Core\SecurityBundle\Context;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Checks if token is white listed
 */
class TokenWhiteList implements TokenWhiteListInterface
{
    /**
     * @type Symfony\Component\DependencyInjection\ContainerInterface 
     */
    private $container;
    
    /**
     * @type Core\SecurityBundle\Context\RightToken
     */
    private $token;
    
    /**
     * @param Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, RightToken $token)
    {
        $this->token = $token;
        $this->container = $container;        
    }
    
    /**
     * Checks if token is white listed
     * 
     * @return boolean true if token is white listed
     */
    public function isWhiteListed()
    {
        return false;
    }
    
}
