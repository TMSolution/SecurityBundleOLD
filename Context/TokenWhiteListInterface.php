<?php

namespace Core\SecurityBundle\Context;

/**
 * Checks if token is white listed
 */
interface TokenWhiteListInterface
{
    
    /**
     * @return bool true if token is white listed
     */
    public function isWhiteListed();
    
}
