<?php

namespace Core\SecurityBundle\Context;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents right context
 */
class RightContext implements RightContextInterface
{
    /**
     * @type int
     */
    const SCOPE_SELF = 1;

    /**
     * @type int
     */    
    const SCOPE_PARENT = 2;

    /**
     * @type int
     */    
    const SCOPE_ALL = 4;

    /**
     * @type ContainerInterface
     */
    private $container;
    
    /**
     * @type SecurityTokenStrategyInterface
     */
    private $tokenStrategy;
    
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->tokenStrategy = $container->get('security_right_token_strategy');        
    }
    
    /**
     * @api
     * @return RightTokenInterface
     */
    public function getToken()
    {
        $rightModel = $this->getModel('Core\SecurityBundle\Entity\Right');
        $objectIdentityModel = $this->getModel('Core\SecurityBundle\Entity\ObjectIdentity');
        $securityToken = $this->container->get('security.token_storage')->getToken();
        if ($securityToken == null) {
            return new NoRightToken();
        }
        return new RightToken($this->getTokenName());
    }

    /**
     * @return int current scope mask
     */
    public function getScope()
    {
        $rightModel = $this->getModel('Core\SecurityBundle\Entity\Right');
        $objectIdentityModel = $this->getModel('Core\SecurityBundle\Entity\ObjectIdentity');
        $rights = $rightModel->findBy([
            'objectidentity' => $objectIdentityModel->findOneBy(
                ['name' => $this->getToken()->getName()
                ]
            )
        ]);
        $scope = self::SCOPE_SELF;
        foreach ($rights as $right) {
            $scope = $scope | $right->getScope()->getMask();
        }
        return $scope;
    }

    /**
     * @return @string
     */
    private function getTokenName()
    {
        return $this->tokenStrategy->getTokenName();
    }

    /**
     * @param string $className
     * @return Core\ModelBundle\Model\Model
     */
    protected function getModel($className)
    {
        return $this->container->get('model_factory')->getModel($className);
    }
}
