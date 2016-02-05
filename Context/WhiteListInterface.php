<?php

namespace Core\SecurityBundle\Context;

/**
 * White list
 */
interface WhiteListInterface
{
    /**
     * @param string $route
     * @return bool true if route is allowed anonymously
     */
    public function allowAnonymous($route);
    
    /**
     * <strong>Client MUST check user token before calling this method</strong>
     *  
     * @param string $route
     * @return bool true if route is allowed for a users
     */
    public function allowUser($route);
    
}
