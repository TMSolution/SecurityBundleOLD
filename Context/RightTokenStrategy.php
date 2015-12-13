<?php

namespace Core\SecurityBundle\Context;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Realize token strategy
 */
class RightTokenStrategy implements RightTokenStrategyInterface {

    /**
     * @type ContainerInterface
     */
    private $container;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;        
    }
    
    /**
     * @return string token name
     */
    public function getTokenName()
    {
                
        $route = $this->container->get('request')->get('_route');
        if ($route == null) {
            return;
        }        
        $params = $this->container->get('request')->attributes->all();
        
        //RouteNotFoundExce Exception 
        $pathInfo = $this->container->get('router')->generate($route, [
            'locale' => 'en',
            'entityName' => $this->container->get('classmapperservice')->translateEntityName($params['entityName'], $params['_locale'], 'en'), 
            'containerName' => $params['containerName'],
            'actionId' => $params['actionId']
        ]);
        $pathInfo = \trim($pathInfo, '/');
        $pathInfo = \str_replace('/', "_", $pathInfo);
        $nodes = \explode('_', $pathInfo);
        if (isset($nodes[0]) && $nodes[0] !== 'panel' && count($nodes) == 1) {
            return '';
        }
        return \implode('_', array_slice($nodes, 0, 2));           
    }
    
}
