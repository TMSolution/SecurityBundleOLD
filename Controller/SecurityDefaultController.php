<?php

namespace Core\SecurityBundle\Controller;


use Core\BaseBundle\Controller\DefaultController;
use Core\SecurityBundle\Annotations\Permissions;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\HttpFoundation\Request;

class SecurityDefaultController extends DefaultController
{

    
    
    public function unauthorizedAction()
    {
        return $this->render('CoreSecurityBundle:Errors:error.html.twig',array('error_message_403'=>'UPS. Access denied !'));
    }
    
    
    

}
