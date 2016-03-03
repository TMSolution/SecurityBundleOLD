<?php

namespace Core\SecurityBundle\Context;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Resolves a right token
 */
class RightContext implements RightContextInterface
{
    const SCOPE_SELF = 1;
    const SCOPE_PARENT = 2;
    const SCOPE_ALL = 4;

    private $container;
    private $whiteList;
    private $name;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->name = $this->getSecurityTokenName();
        $this->whiteList = $container->get('security_whitelist');
    }
    
    public function getToken()
    {
        $rightModel = $this->getModel('Core\SecurityBundle\Entity\Right');
        $objectIdentityModel = $this->getModel('Core\SecurityBundle\Entity\ObjectIdentity');
        $token = $this->container->get('security.token_storage')->getToken();
        if ($token == null) {
            return new NoRightToken();
        }
        
        if ($this->isWhiteListed()) {
            return new RightToken($this->getSecurityTokenName(), true);
        }        
        
        try {
            $objectIdentity = $objectIdentityModel->findOneBy(['name' => $this->getSecurityTokenName()]);
            $rights = $rightModel
                ->getQueryBuilder()
                ->select('COUNT(u)')
                ->where('u.user = :u')
                ->andWhere('u.objectidentity = :o')
                ->setParameter('u', $token->getUser())
                ->setParameter('o', $objectIdentity)
                ->getQuery()
                ->getScalarResult();
            return new RightToken($this->getSecurityTokenName(), $this->isWhiteListed());
        } catch(EntityNotFoundException $e) {
            return new NoRightToken($this->getSecurityTokenName());
        }
    }

    private function getSecurityTokenName()
    {
        $pathInfo = $this->container->get('request')->getPathInfo();
        $pathInfo = \trim($pathInfo, '/');
        $pathInfo = str_replace('/', "_", $pathInfo);
        $nodes = \explode('_', $pathInfo);
        if (isset($nodes[0]) && $nodes[0] !== 'panel' && count($nodes) == 1) {
            return '';
        }
        return implode('_', array_slice($nodes, 0, 2));
    }

    /**
     * @return boolean route is white listed
     */
    private function isWhiteListed()
    {
        $route = $this->container->get('request')->get('_route');
        $securityContext = $this->container->get('security.authorization_checker');
        
        if ($securityContext->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')) {        
            if ($this->whiteList->allowAnonymous($route)) {
                return true;
            }
        }
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($this->whiteList->allowUser($route)) {
                return true;
            }
        }
        return false;
        
    }

    /**
     * Resolve a data scope
     * 
     * @return int current data scope
     */
    public function getScope()
    {
        $rightModel = $this->getModel('Core\SecurityBundle\Entity\Right');
        $objectIdentityModel = $this->getModel('Core\SecurityBundle\Entity\ObjectIdentity');
        $right = $rightModel->findOneBy([
            'objectidentity' => $objectIdentityModel->findOneBy([
                'name' => $this->getToken()->getName()
            ]),
            'user' => $this->contextUser()
        ]);
        return self::SCOPE_SELF | $right->getScope()->getMask();
    }
    
    protected function getModel($className) {
        return $this->container->get('model_factory')->getModel($className);
    }   
    
    protected function contextUser() {
        return $this->container->get('security.token_storage')->getToken()->getUser();
    }
}
