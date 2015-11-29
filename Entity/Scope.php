<?php

namespace Core\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Scope
 *
 * @ORM\Table("security_scope")
 * @ORM\Entity
 */
class Scope
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
    
    /**
     * @ORM\Column(type="string", name="scope", unique=true, length=70)
     */
    private $scope;

    /**
     * @ORM\Column(type="smallint", name="mask", unique=true)
     */
    private $mask;
    
    /**
     * Populate the scope field
     * @param string $role SCOPE_OWNER | SCOPE_PARENT | SCOPE_ALL etc
     * @param int $mask
     */
    public function __construct($scope, $mask)
    {
        $this->scope = $scope;
        $this->mask = $mask;
    }

    public function getId()
    {
        return $this->id;
    }
    

    public function getScope()
    {
        return $this->scope;
    }
    
    public function setScope($scope)
    {
        $this->scope = $scope;
        
        return $this;
    }

    public function getMask()
    {
        return $this->mask;
    }

    public function setMask($mask)
    {
        $this->mask = $mask;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->scope;
    }



}
