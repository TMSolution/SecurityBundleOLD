<?php

namespace Core\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="security_objectidentitytype")
 */
class ObjectIdentityType {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options= {"comment":"[PODSTAWOWE ELEMENTY SYSTEMU]Tabela słownikowa zawiera informacje podsystemu zabezpieczeń."})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

      


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ObjectIdentityType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    
    public function __toString()
    {
        return $this->name;
    }


}
