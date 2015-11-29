<?php

namespace Core\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Role
 *
 * @ORM\Table("fos_role")
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
     * @ORM\Column(type="string", name="scope", unique=true, length=70)
     */
    private $role;
    
    
    /**
     * @ORM\ManyToMany(targetEntity="CCO\UserBundle\Entity\User", inversedBy="rolesCollection")
     * @ORM\JoinTable(name="callcenter_user_has_role", 
     *      joinColumns={ @ORM\JoinColumn(name="role_id", referencedColumnName="id") },
     *      inverseJoinColumns={ @ORM\JoinColumn(name="user_id", referencedColumnName="id")})
     */
    protected $users;
    
    /**
     * Populate the scope field
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
     * Return the scope field.
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
     * Return the scope field.
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
