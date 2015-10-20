<?php

namespace Core\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 * @ORM\Table(name="security_objectidentity")
 */
class ObjectIdentity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options= {"comment":"[PODSTAWOWE ELEMENTY SYSTEMU]Tabela zawiera informacje podsystemu zabezpieczeń."})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     *
     * @ORM\Column(type="string",length=200)
     */
    protected $name;

    /**
     *
     * @ORM\Column(type="string",length=200)
     */
    protected $displayName;
    
    /**
     *
     * @ORM\Column(type="boolean",options={"default":true})
     */
    protected $isBusinessObject;

    /**
     * @ORM\ManyToOne(targetEntity="ObjectIdentityType")
     * @ORM\JoinColumn(name="objectidentitytype_id", referencedColumnName="id")
     */
    protected $objectIdentityType;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     * 
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="ObjectIdentity", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * 
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="ObjectIdentity", mappedBy="parent")
     * 
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity="Right", mappedBy="objectidentity")
     */
    private $rights;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rights = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * @return ObjectIdentity
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get display name
     *
     * @return string 
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }
    
    /* Set display name
     *
     * @param string $name
     * @return ObjectIdentity
     */
    public function setDisplayName($name)
    {
        $this->displayName = $name;

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
    

    /**
     * Set isBusinessObject
     *
     * @param boolean $isBusinessObject
     * @return ObjectIdentity
     */
    public function setIsBusinessObject($isBusinessObject)
    {
        $this->isBusinessObject = $isBusinessObject;

        return $this;
    }

    /**
     * Get isBusinessObject
     *
     * @return boolean 
     */
    public function getIsBusinessObject()
    {
        return $this->isBusinessObject;
    }

    /**
     * Set lft
     *
     * @param integer $lft
     * @return ObjectIdentity
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer 
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     * @return ObjectIdentity
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl
     *
     * @return integer 
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     * @return ObjectIdentity
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer 
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set root
     *
     * @param integer $root
     * @return ObjectIdentity
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root
     *
     * @return integer 
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set objectIdentityType
     *
     * @param \Core\SecurityBundle\Entity\ObjectIdentityType $objectIdentityType
     * @return ObjectIdentity
     */
    public function setObjectIdentityType(\Core\SecurityBundle\Entity\ObjectIdentityType $objectIdentityType = null)
    {
        $this->objectIdentityType = $objectIdentityType;

        return $this;
    }

    /**
     * Get objectIdentityType
     *
     * @return \Core\SecurityBundle\Entity\ObjectIdentityType 
     */
    public function getObjectIdentityType()
    {
        return $this->objectIdentityType;
    }

    /**
     * Set parent
     *
     * @param \Core\SecurityBundle\Entity\ObjectIdentity $parent
     * @return ObjectIdentity
     */
    public function setParent(\Core\SecurityBundle\Entity\ObjectIdentity $parent = null)
    {
        $this->parent = $parent;
        
        return $this;
    }

    /**
     * Get parent
     *
     * @return \Core\SecurityBundle\Entity\ObjectIdentity 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param \Core\SecurityBundle\Entity\ObjectIdentity $children
     * @return ObjectIdentity
     */
    public function addChild(\Core\SecurityBundle\Entity\ObjectIdentity $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Core\SecurityBundle\Entity\ObjectIdentity $children
     */
    public function removeChild(\Core\SecurityBundle\Entity\Permission $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }
    
    public function __toString()
    {
        return $this->name;
    }

}
