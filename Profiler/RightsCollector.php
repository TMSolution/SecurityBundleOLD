<?php

namespace Core\SecurityBundle\Profiler;

use AppKernel;
use Core\SecurityBundle\Context\RightToken;
use Exception;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RightsCollector implements DataCollectorInterface
{
    
    private $tokenClass;
    private $tokenName;
    private $scope;
    
    public function collect(Request $request, Response $response, Exception $exception = null)
    {
        $kernel = new \AppKernel('dev', true);
        $kernel->boot();
        $container = $kernel->getContainer();   
        $container->enterScope('request');
        $container->set('request', $request, 'request');
        $securityRightContext = $container->get('security_right_context');
        $token = $securityRightContext->getToken();
        $this->tokenClass = get_class($token);
        $this->tokenName = $token->getName();
        if ($token instanceof RightToken) {
            $this->scope = $securityRightContext->getScope();
        }
    }

    public function getTokenClass()
    {
        return $this->tokenClass;
    }

    public function getTokenName()
    {
        return $this->tokenName;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function getName()
    {
        return "rights";
    }
}
