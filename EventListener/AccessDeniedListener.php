<?php

namespace Core\SecurityBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Description of AccessDeniedListener
 *
 * @author krzysztek
 */
class AccessDeniedListener
{

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {

        if (get_class($event->getException()) == 'Symfony\Component\Security\Acl\Exception\NoAceFoundException') {

            try {
                //$this->backWithMessage($event);
                $this->accessDenied($event);
            } catch (\Exception $e) {
                $this->accessDenied($event);
            }
        }
         elseif (get_class($event->getException()) == 'Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException') {
            $this->accessDenied($event);
        }
    }

    protected function backWithMessage($event)
    {
        $this->container
                ->get('request')
                ->getSession()
                ->getFlashBag()
                ->add('danger', 'Yo have no rights!');
        $referer = $this->container
                ->get('request')
                ->headers
                ->get('referer');

        $event->setResponse(new RedirectResponse($referer));
    }

    protected function accessDenied($event)
    {
        $event->setResponse(new RedirectResponse($this->container->get('request')->getUriForPath('/unauthorized')));
        throw new AccessDeniedHttpException($this->container->get('request')->getUriForPath('/unauthorized'));
    }

}
