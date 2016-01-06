<?php

namespace Core\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="security_right")
 * 
 */
class Right
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options= {"comment":"[PODSTAWOWE ELEMENTY SYSTEMU]Tabela słownikowa zawierająca informacje na temat typów uprawnień."})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $viewRight;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $editRight;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $masterRight;

    /**
     * @ORM\ManyToOne(targetEntity="Core\SecurityBundle\Model\SecureUserInterface")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Core\SecurityBundle\Entity\Role")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     *
     */
    protected $role;

    /**
     * @ORM\ManyToOne(targetEntity="Core\SecurityBundle\Entity\ObjectIdentity")
     * @ORM\JoinColumn(name="objectidentity_id", referencedColumnName="id")
     */
    protected $objectidentity;

    /**
     * @ORM\ManyToOne(targetEntity="Core\SecurityBundle\Entity\Scope")
     */
    protected $scope;


    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        $this->name;
    }

    public function setName($param)
    {
        $this->name = $param;
        return $this;
    }

    public function getViewRight()
    {
        return $this->viewRight;
    }

    public function setViewRight($param)
    {
        $this->viewRight = $param;
        return $this;
    }

    public function getEditRight()
    {
        return $this->editRight;
    }

    public function setEditRight($param)
    {
        $this->editRight = $param;
        return $this;
    }

    public function getMasterRight()
    {
        return $this->masterRight;
    }

    public function setMasterRight($param)
    {
        $this->masterRight = $param;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(\Core\SecurityBundle\Model\SecureUserInterface $user = null)
    {
        $this->user = $user;
        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole(Role $role = null)
    {
        $this->role = $role;
        return $this;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function setScope(Scope $scope = null)
    {
        $this->scope = $scope;
        return $this;
    }
    
    public function getObjectIdentity()
    {
        return $this->objectidentity;
    }

    public function setObjectIdentity(\Core\SecurityBundle\Entity\ObjectIdentity $objectIdentity = null)
    {
        $this->objectidentity = $objectIdentity;
        return $this;
    }
    
    
    


    /**
     * __toString method
     *
     * return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }

}
