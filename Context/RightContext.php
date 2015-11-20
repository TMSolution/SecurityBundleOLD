<?php

namespace Core\SecurityBundle\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RightContext implements RightContextInterface
{
    private $container;
    private $name;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;        
        $this->name = $this->getName();
    }
    
    public function getToken()
    {
        return new RightToken($this->getName());
    }

    private function getName() {
        $route = $this->container->get('request')->get('_route');
        $pathInfo = $this->container->get('request')->getPathInfo();
        $pathInfo = \trim($pathInfo, '/');        
        $pathInfo = str_replace('/', "_", $pathInfo);
        $nodes = \explode('_', $pathInfo);        
        if (isset($nodes[0]) && $nodes[0] !== 'panel' && count($nodes) == 1) {
            return '';
        }
        return implode('_', array_slice($nodes, 0, 2));
    }
    
}
