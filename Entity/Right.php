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
     * @ORM\ManyToOne(targetEntity="TMSolution\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="TMSolution\UserBundle\Entity\Role")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     *
     */
    protected $role;

    /**
     * @ORM\ManyToOne(targetEntity="Core\SecurityBundle\Entity\ObjectIdentity")
     * @ORM\JoinColumn(name="objectidentity_id", referencedColumnName="id")
     */
    protected $objectidentity;

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

    public function setUser(\TMSolution\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole(\TMSolution\UserBundle\Entity\Role $role = null)
    {
        $this->role = $role;
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
    
    
    

}
