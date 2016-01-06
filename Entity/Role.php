<?php

namespace Core\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Role
 *
 * @ORM\Table("security_role")
 * @ORM\Entity
 */
class Role  {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="string", name="name", unique=true, length=70)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", name="technical_name", unique=true, length=70)
     */
    private $technicalName;

    /**
     * @ORM\ManyToMany(targetEntity="CCO\UserBundle\Entity\User", mappedBy="baseRoles")
     */
    protected $users;
    
     /**
     * @ORM\Column(type="boolean", name="is_call_center_role")
     */
    protected $isCallCenterRole;

  
    /**
     * Get id
     * 
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Return the name field.
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Return the technicalName field.
     * @return string 
     */
    public function getTechnicalName() {
        return $this->technicalName;
    }

    /**
     * Return the role field.
     * @return string 
     */
    public function __toString() {
        return (string) $this->name;
    }

    /**
     * Add users
     *
     * @param \CCO\UserBundle\Entity\User $users
     * @return Role
     */
    public function addUser(\CCO\UserBundle\Entity\User $users) {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \CCO\UserBundle\Entity\User $users
     */
    public function removeUser(\CCO\UserBundle\Entity\User $users) {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers() {
        return $this->users;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set technicalName
     *
     * @param string $technicalName
     *
     * @return Role
     */
    public function setTechnicalName($technicalName)
    {
        $this->technicalName = $technicalName;

        return $this;
    }

    /**
     * Set isCallCenterRole
     *
     * @param boolean $isCallCenterRole
     *
     * @return Role
     */
    public function setIsCallCenterRole($isCallCenterRole)
    {
        $this->isCallCenterRole = $isCallCenterRole;

        return $this;
    }

    /**
     * Get isCallCenterRole
     *
     * @return boolean
     */
    public function getIsCallCenterRole()
    {
        return $this->isCallCenterRole;
    }
}
