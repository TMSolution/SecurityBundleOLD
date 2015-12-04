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
class Role implements RoleInterface
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
    
    /**
     * @ORM\Column(type="string", name="role", unique=true, length=70)
     */
    private $role;
    
   
    /**
<<<<<<< HEAD
     * @ORM\ManyToMany(targetEntity="CCO\UserBundle\Entity\User", mappedBy="rolesCollection")
=======
     * @ORM\ManyToMany(targetEntity="CCO\UserBundle\Entity\User", inversedBy="rolesCollection")
>>>>>>> c01e8ca0b324058e8fcaf63ce9194f011982a652
     */
    protected $users;
    
    /**
     * Populate the role field
     * @param string $role ROLE_FOO etc
     */
   /* public function __construct($role)
    {
        $this->role = $role;
    }*/

    /**
     * Get id
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Return the role field.
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }
    
    public function setRole($role)
    {
        $this->role = $role;
        
        return $this;
    }

    /**
     * Return the role field.
     * @return string 
     */
    public function __toString()
    {
        return (string) $this->role;
    }


    /**
     * Add users
     *
     * @param \CCO\UserBundle\Entity\User $users
     * @return Role
     */
    public function addUser(\CCO\UserBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \CCO\UserBundle\Entity\User $users
     */
    public function removeUser(\CCO\UserBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

}
