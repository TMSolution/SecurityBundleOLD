<?php

namespace Core\SecurityBundle\Context;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RightContext implements RightContextInterface
{
    const SCOPE_SELF = 1;
    const SCOPE_PARENT = 2;
    const SCOPE_ALL = 4;

    private $container;
    private $name;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->name = $this->getSecurityTokenName();
    }
    
    public function getToken()
    {
        $rightModel = $this->getModel('Core\SecurityBundle\Entity\Right');
        $objectIdentityModel = $this->getModel('Core\SecurityBundle\Entity\ObjectIdentity');
        $token = $this->container->get('security.token_storage')->getToken();
        if ($token == null) {
            return new NoRightToken();
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
            return new RightToken($this->getSecurityTokenName());
        } catch(EntityNotFoundException $e) {
            return new NoRightToken();
        }
    }

    private function getSecurityTokenName()
    {
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

    public function getScope()
    {
        $rightModel = $this->container->get('model_factory')->getModel('Core\SecurityBundle\Entity\Right');
        $objectIdentityModel = $this->container->get('model_factory')->getModel('Core\SecurityBundle\Entity\ObjectIdentity');
        $rights = $rightModel->findBy([
            'objectidentity' => $objectIdentityModel->findOneBy(['name' => $this->getToken()->getName()])
        ]);
        $scope = self::SCOPE_SELF;
        foreach ($rights as $right) {
            $scope = $scope | $right->getScope()->getMask();
        }
        return $scope;
    }

    protected function getModel($className) {
        return $this->container->get('model_factory')->getModel($className);
    }
}
