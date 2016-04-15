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
     * @ORM\Column(type="integer", name="priority")
     */    
    private $priority;
    
    /**
     * Populate the scope field
     * @param string $role ROLE_FOO etc
     */
    public function __construct($role)
    {
        $this->role = $role;
    }

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
     * Get priority
     * 
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set priority
     * 
     * @param int
     * @return self
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        
        return $this;
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

}
