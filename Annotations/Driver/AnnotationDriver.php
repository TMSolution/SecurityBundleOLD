<?php

namespace Core\SecurityBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader; //This thing read annotations
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

    //@todo:obsłużyć wszystkie możliwe wyjątki
    public function checkRights($rights)
    {
        
        exit();
        $aclProvider = $this->container->get('security.acl.provider');
        $masterRequest = $this->container->get('request_stack')->getMasterRequest();
        $objectName = $this->container->get("classmapperservice")->getEntityClass($masterRequest->attributes->get('entityName'), $masterRequest->getLocale());

        $route = $this->container->get('request')->get('_route');
        $pathInfo = $this->container->get('request')->getPathInfo();
        $pathInfo = \trim($pathInfo, '/');        
        $pathInfo = str_replace('/', "_", $pathInfo);
        $nodes = \explode('_', $pathInfo);
        
        if (isset($nodes[0]) && $nodes[0] !== 'panel' && count($nodes) == 1) {
            return;
        }
        
        $classIdentity = new ObjectIdentity(
            implode('_', array_slice($nodes, 0, 2)),
            'link'
        );        
   
        
        // throws AclNotFoundException
        $acl = $aclProvider->findAcl($classIdentity);   
//       
//        dump($classIdentity);
//        exit();
//       
        $user = $this->container->get('security.context')->getToken()->getUser();        
        
        $securityIdentities = [];
        $securityIdentities[]= new UserSecurityIdentity($user->getEmail(), 'TMSolution\UserBundle\Entity\User');
        
        foreach ($user->getRoles() as $role) {
            $securityIdentities[] = new RoleSecurityIdentity($role);
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
