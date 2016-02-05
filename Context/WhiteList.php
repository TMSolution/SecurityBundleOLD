<?php
namespace Core\SecurityBundle\Context;

/**
 * White list
 */
class WhiteList implements WhiteListInterface
{    
    private $anonymousRoutes;
    private $userRoutes;
    
    /**
     * @param array $anonymousRoutes
     * @param array $userRoutes
     */
    public function __construct(
        array $anonymousRoutes,
        array $userRoutes)
    {
        $this->anonymousRoutes = $anonymousRoutes;
        $this->userRoutes = $userRoutes;
    }

    /**
     * @inheritdoc
     */
    public function allowAnonymous($route)
    {
        return in_array($route, $this->anonymousRoutes);        
    }

    /**
     * @inheritdoc
     */
    public function allowUser($route)
    {
        return in_array($route, $this->userRoutes);        
    }

}