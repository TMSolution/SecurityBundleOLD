<?php

namespace Core\SecurityBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader; //This thing read annotations
use Symfony\Component\HttpKernel\Event\FilterControllerEvent; //Use essential kernel component
use Core\SecurityBundle\Annotations; //Use our annotation
use Core\SecurityBundle\Annotations\Permissions; //In this class I check correspondence permission to user
use Symfony\Component\HttpFoundation\Response; // For example I will throw 403, if access denied
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
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
//        $aclProvider = $this->container->get('security.acl.provider');
//        $masterRequest = $this->container->get('request_stack')->getMasterRequest();
//        $objectName = $this->container->get("classmapper")->getEntityClass($masterRequest->attributes->get('entityName'), $masterRequest->getLocale());
//
//        $classIdentity = new ObjectIdentity($objectName, 'class');
//        $user = $this->container->get('security.context')->getToken()->getUser();
//
//        $acl = $aclProvider->findAcl($classIdentity);   
//        if ($this->type == "module") {
//            $parentAcl = $acl->getParentAcl();
//            if ($parentAcl) {
//                $acl = $parentAcl;
//            } else {
//                throw new \Exception("Brak rodzica wskazanego obiektu");
//            }
//        }
//        
//        $securityIdentities = [];
//        foreach ($user->getRoles() as $role) {
//            $securityIdentities[] = new RoleSecurityIdentity($role);
//        }
//        $acl->isGranted($rights, $securityIdentities);
        
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
