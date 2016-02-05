<?php

namespace Core\SecurityBundle\Annotations\Driver;

use Core\SecurityBundle\Context\NoRightToken;
use Doctrine\Common\Annotations\Reader; //This thing read annotations
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent; //Use essential kernel component
use Core\SecurityBundle\Annotations; //Use our annotation
use Core\SecurityBundle\Annotations\Permissions; //In this class I check correspondence permission to user
use Symfony\Component\HttpFoundation\Response; // For example I will throw 403, if access denied
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;

class AnnotationDriver
{

    private $reader;
    protected $container;

    public function __construct($reader, $container, $type)
    {
        $this->reader = $reader; //get annotations reader
        $this->container = $container;
        $this->type = $type;
    }

    /**
     * @param $rights
     */
    public function checkRights($rights)
    {
        
        $rightToken = $this->container->get('security_right_context')->getToken();
        if ($rightToken instanceof NoRightToken) {
            if ($rightToken->getName() == "_fragment") {
                return;
            }
            throw new AccessDeniedException(
                sprintf(
                    'Access to \'%s\' was denied',
                    $rightToken->getName()
                )
            );
        }
 
        if ($rightToken->isWhiteListed() === true) {
            return;
        }
        
               
        
        $classIdentity = new ObjectIdentity($rightToken->getName(), 'link');
        $aclProvider = $this->container->get('security.acl.provider');
        $acl = $aclProvider->findAcl($classIdentity); 
        $acl->setEntriesInheriting(false);

        
        $user = $this->container->get('security.context')->getToken()->getUser();        
        $securityIdentities = [];
        $securityIdentities[]= new UserSecurityIdentity($user->getEmail(), 'TMSolution\UserBundle\Entity\User');    
        foreach ($user->getRoles() as $role) {
            $securityIdentities[] = new RoleSecurityIdentity($role->getRole());
        }

        $acl->isGranted($rights, $securityIdentities);        

    }

    /**
     * This event will fire during any controller call
     */
    public function onKernelController(FilterControllerEvent $event)
    {

        if (!is_array($controller = $event->getController())) { //return if no controller
            return;
        }

        $object = new \ReflectionObject($controller[0]); // get controller
        $method = $object->getMethod($controller[1]); // get method

        foreach ($this->reader->getMethodAnnotations($method) as $configuration) { //Start of annotations reading
            if (isset($configuration->rights)) {
                $this->checkRights($configuration->rights);
            }
        }
    }

}
