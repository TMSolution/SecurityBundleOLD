<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core\SecurityBundle\EventListener;

use Core\SecurityBundle\Context\NoRightToken;
use Core\SecurityBundle\Context\RightContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Core\SecurityBundle\Context\RightTokenInterface;
use Core\SecurityBundle\Annotations\Permissions;
use ReflectionClass;
use ReflectionMethod;
use sprintf;

/**
 * SecurityListener
 *
 * @author krzysztek
 */
class SecurityListener {

    /**
     * @type Symfony\Component\DependencyInjection\ContainerInterface 
     */
    private $container;

    /**
     * @type use Doctrine\Common\Annotations\AnnotationReader; 
     */
    private $reader;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->reader = $this->container->get('annotation_reader');
    }

    /**
     * @api
     * @param Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        return false;
        $securityToken = $this->getSecurityToken();        
        if ($securityToken instanceof AnonymousToken) {
            // Let anonymous to log in
            throw new AccessDeniedHttpException(
                'Anonymous user has been blocked'
            );
        }
        
        $controller = $event->getController();
        if (!is_array($controller)) {
            throw new AccessDeniedHttpException(
                'Every action must be handled by the controller'
            );            
        }
        
        $action = (new ReflectionClass($controller[0]))
            ->getMethod($controller[1]);
        
        $permission = $this->readPermission($action);
        
        if (!isset($permissions->rights)) {
            throw new AccessDeniedHttpException(
                sprintf(
                    'Missing or invalid Permissions annotation for method %s in class %s',
                    $action->getName(),
                    $action->getDeclaringClass()->getName()
                )
            );
        }       
        
        $this->ensureRights($permissions->rights);
        
    }
    
    /**
     * @param ReflectioMethod $action
     * @return Core\SecurityBundle\Annotations\Permissions
     */
    private function readPermission(ReflectionMethod $action)
    {
        foreach ($this->reader->getMethodAnnotations($action) as $annotation) {
            if ($annotation instanceof Permissions) {
                return $annotation;
            }
        }            
        return new Permissions;
    }
    
    /**
     * @param array
     * @throws Symfony\Component\Security\Acl\Exception\AclNotFoundException
     */
    private function ensureRights(array $annotation)
    {        
        $rightToken = $this->container->get('security_right_context')->getToken();
        if ($this->ensureWhiteListed($rightToken) === true) {
            return; // Always granted if white listed
        }
        exit('STOP');
        $aclProvider = $this->container->get('security.acl.provider');
        $masterRequest = $this->container->get('request_stack')->getMasterRequest();
        $classIdentity = new ObjectIdentity(
            implode('_', array_slice($nodes, 0, 2)),
            'link'
        );                
        $acl = $aclProvider->findAcl($classIdentity); 
        $user = $this->container->get('security.context')->getToken()->getUser();                
        $securityIdentities = [];
        $securityIdentities[]= new UserSecurityIdentity(
            $user->getEmail(), 
            'CCO\UserBundle\Entity\User'
        );
        foreach ($user->getRoles() as $role) {
            $securityIdentities[] = new RoleSecurityIdentity($role->getRole());
        }
        $acl->isGranted($rights, $securityIdentities);        
    }
    
    /**
     * @param \Core\SecurityBundle\Context\RightToken
     * @return bool true if white listed
     */
    private function ensureIsWhiteListed(RightToken $token)
    {
        return $this->get('security_right_whitelist')->isWhiteListed($token);
    }

    /**
     * @return Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
     */
    private function getSecurityToken()
    {
        return $this->container->get('security.token_storage')->getToken();
    }
    
}
