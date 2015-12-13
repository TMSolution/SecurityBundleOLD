<?php

namespace Core\SecurityBundle\Model;

use Core\ModelBundle\Model\Model as BaseModel;
use Core\SecurityBundle\Entity\Right as RightEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity as SecurityObjectIdentity;
use Symfony\Component\Yaml\Exception\ParseException;
use DomainException;
use Doctrine\ORM\EntityNotFoundException;
use OutOfBoundsException;

class ObjectIdentity extends BaseModel
{

    /**
     * Get all resources with identity 'link'
     *
     * @return array
     */
    public function getLinks()
    {
        $repo = $this->getRepository();
        $rootNode = $repo->findOneById(1);
        $query = $repo->getNodesHierarchyQueryBuilder(
                        $rootNode, false, [], false
                )
                ->select('node')
                //->orderBy('u.root, u.lft', 'ASC')
                ->where('node.objectIdentityType = 5')
                ->getQuery();
        //$options = array('decorate' => true);
        $options = [];
        $tree = $repo->buildTree($query->getArrayResult(), $options);
        return $tree;
    }

//Usunięcie uprawnienia wiąże się z usunięciem objectIdentity z Acla
//Dodanie uprawniena  wiąże się z dodaniem  objectIdentity do Acla
//Przeciążyć metody save, delete, update?
//uprawnienia musza być zapisywane natychmiast

    protected function checkExecuteImmediately($executeImmediately)
    {
        if (false == $executeImmediately) {
            throw new \InvalidArgumentException('Security operations must be save immediately,  $executeImmediately parameter must be set as true');
        }
    }

    protected function createClassIdentity($entityObject)
    {
        return new SecurityObjectIdentity($entityObject->getName(), $entityObject->getObjectIdentityType()->getName());
    }

    protected function createACL($entityObject)
    {
        $aclProvider = $this->container->get('security.acl.provider');
        $classIdentity = $this->createClassIdentity($entityObject);

//        try {
//            $acl = $aclProvider->findAcl($classIdentity);
//        } catch (\Exception $e) {
        $acl = $aclProvider->createAcl($classIdentity);
//        } finally {
//            //
//        }

        $parentObject = $entityObject->getParent();


        if ($parentObject) {
            $parentAcl = $aclProvider->findAcl($this->createClassIdentity($parentObject));
            $acl->setParentAcl($parentAcl);
            $aclProvider->updateAcl($acl);
        }
    }

    protected function updateACL($entityObject)
    {
        $aclProvider = $this->container->get('security.acl.provider');

        //wyznaczenie identyfikatora dla klasy
        //$classIdentity = new SecurityObjectIdentity($entityObject->getName(), $entityObject->getObjectIdentityType()->getName());
        //obsługa utworzenia ACL - createAcl zwraca błąd jeśli już istnieje
        //try {
        $acl = $this->createAcl($entityObject);
        /* } catch (\Exception $e) {
          //istnieje już acl

          die('asdasdsadsad');
          } */
    }

    public function create($entityObject, $executeImmediately = false, $logOperation = false, $checkRights = true)
    {
        $this->checkExecuteImmediately($executeImmediately);
        parent::create($entityObject, false, $logOperation, $checkRights);
        $this->createACL($entityObject);
        if ($executeImmediately == true) {
            $this->flush();
        }
    }

    public function createEntities(ArrayCollection $arrayCollection, $executeImmediately = false, $checkRights = true)
    {
        $this->checkExecuteImmediately($executeImmediately);
        parent::createEntities($arrayCollection, $executeImmediately, $checkRights);
        foreach ($arrayCollection as $entityObject) {
            $this->createACL($entityObject);
        }
    }

    public function delete($entityObject, $executeImmediately = false, $logOperation = false, $checkRights = true)
    {
        $this->checkExecuteImmediately($executeImmediately);
        parent::delete($entityObject, $executeImmediately, $logOperation, $checkRights);
        //@todo delete object identity
    }

    public function update($entityObject, $executeImmediately = true, $logOperation = false, $checkRights = true)
    {
        $this->checkExecuteImmediately($executeImmediately);
        parent::update($entityObject, $executeImmediately, $logOperation, $checkRights);
        $this->updateACL($entityObject);
    }

    public function synchrozieObjectIdentity()
    {
        foreach ($this->getRepository()->findBy(array(), array('parent' => 'asc')) as $entityObject) {
            $this->createACL($entityObject);
        }
    }

    public function getModules()
    {
        return $this->findBy(['objectIdentityType' => 2]);
    }

    public function mergeRights(array $rights, array $rights2)
    {        
        $objectIdentityModel = $this->getModelFactory()->getModel('Core\SecurityBundle\Entity\ObjectIdentity');
        $scopeModel = $this->getModelFactory()->getModel('Core\SecurityBundle\Entity\Scope');        
        $createClosure = function (array $item) use ($objectIdentityModel, $scopeModel) {
            $objectIdentity = $objectIdentityModel->findOneById($item['module_id']);
            $scope = $scopeModel->findOneById($item['scope_id']);
            $right = [];            
            $right["objectidentity_id"] = $objectIdentity->getId();
            $right["objectidentity_name"] = $objectIdentity->getName();
            $right["objectidentity_displayName"] = $objectIdentity->getDisplayName();            
            $right["right_id"] = null;            
            $right["right_viewRight"] = isset($item['viewRight']) ? (bool) $item['viewRight'] : false;
            $right["right_editRight"] = isset($item['editRight']) ? (bool) $item['editRight'] : false;
            $right["right_masterRight"] = isset($item['masterRight']) ? (bool) $item['masterRight'] : false;            
            if($right["right_masterRight"] === true) {
                $right["right_editRight"] = true;
            } 
            if ($right["right_editRight"] === true) {                
                $right["right_viewRight"] = true;
            }            
            $right["right_scope_id"] = $scope->getId();
            $right["right_scope_mask"] = $scope->getMask();
            return $right;
        };        
        $rightMatrix = array_merge($rights, $rights2);        
        $rightSet = [];
        foreach ($rightMatrix as $right) {
            $item = $createClosure($right);
            if (isset($rightSet[$item['objectidentity_name']])) {
                $rightSetItem = $rightSet[$item['objectidentity_name']];
                if ($item['right_viewRight'] === true) {
                    $rightSet[$item['objectidentity_name']]['right_viewRight'] = true;
                }
                if ($item['right_editRight'] === true) {
                    $rightSet[$item['objectidentity_name']]['right_editRight'] = true;
                }
                if ($item['right_masterRight'] === true) {
                    $rightSet[$item['objectidentity_name']]['right_masterRight'] = true;
                }
                if ($rightSetItem['right_scope_mask'] < $item['right_scope_mask']) {
                    $rightSet[$item['objectidentity_name']]['right_scope_mask'] = $item['right_scope_mask'];
                    $rightSet[$item['objectidentity_name']]['right_scope_id'] = $item['right_scope_id'];
                }
                continue;
            }
            $rightSet[$item['objectidentity_name']] = $item;
        }        
        return $rightSet;        
    }
    
    /**
     * Get role matrix from the request
     *
     * @param UserInterface $user
     * @return array right Matrix
     */
    public function getRequestedRights(array $request)
    {
        $objectIdentityModel = $this->getModelFactory()->getModel('Core\SecurityBundle\Entity\ObjectIdentity');
        $scopeModel = $this->getModelFactory()->getModel('Core\SecurityBundle\Entity\Scope');        
        $createClosure = function (array $item) use ($objectIdentityModel, $scopeModel) {
            $objectIdentity = $objectIdentityModel->findOneById($item['module_id']);
            $scope = $scopeModel->findOneById($item['scope_id']);
            $right = [];            
            $right["objectidentity_id"] = $objectIdentity->getId();
            $right["objectidentity_name"] = $objectIdentity->getName();
            $right["objectidentity_displayName"] = $objectIdentity->getDisplayName();            
            $right["right_id"] = null;            
            $right["right_viewRight"] = isset($item['viewRight']) ? (bool) $item['viewRight'] : false;
            $right["right_editRight"] = isset($item['editRight']) ? (bool) $item['editRight'] : false;
            $right["right_masterRight"] = isset($item['masterRight']) ? (bool) $item['masterRight'] : false;            
            if($right["right_masterRight"] === true) {
                $right["right_editRight"] = true;
            } 
            if ($right["right_editRight"] === true) {                
                $right["right_viewRight"] = true;
            }            
            $right["right_scope_id"] = $scope->getId();
            $right["right_scope_mask"] = $scope->getMask();
            return $right;
        };
        
        $rightSet = [];
        foreach ($request as $right) {
            $item = $createClosure($right);
            $rightSet[$item['objectidentity_name']] = $item;
        }
        
        return $rightSet;
    }  
    
    /**
     * Get role matrix
     *
     * Join role and user rights.
     *
     * @todo duplicates getRightMatrix
     * @param UserInterface $user
     * @return array right Matrix
     */
    public function getRoleMatrix(array $roles)
    {
        $rightModel = $this->getModelFactory()->getModel('Core\SecurityBundle\Entity\Right');
        $rights = $rightModel->getQueryBuilder()
            ->orWhere('u.role in (:role)')
            ->setParameter('role', $roles)
            ->getQuery()
            ->getResult();
        
        $rightSet = [];
        foreach ($rights as $right) {
            $item = $this->createRightSetItem($right);
            if (isset($rightSet[$item['objectidentity_name']])) {
                $rightSetItem = $rightSet[$item['objectidentity_name']];
                if ($item['right_viewRight'] === true) {
                    $rightSet[$item['objectidentity_name']]['right_viewRight'] = true;
                }
                if ($item['right_editRight'] === true) {
                    $rightSet[$item['objectidentity_name']]['right_editRight'] = true;
                }
                if ($item['right_masterRight'] === true) {
                    $rightSet[$item['objectidentity_name']]['right_masterRight'] = true;
                }
                if ($rightSetItem['right_scope_mask'] < $item['right_scope_mask']) {
                    $rightSet[$item['objectidentity_name']]['right_scope_mask'] = $item['right_scope_mask'];
                    $rightSet[$item['objectidentity_name']]['right_scope_id'] = $item['right_scope_id'];
                }
                continue;
            }
            $rightSet[$item['objectidentity_name']] = $item;
        }
        
        return $rightSet;
    }    

    /**
     * Get right matrix
     *
     * Join role and user rights.
     *
     * @param UserInterface $user
     * @return array right Matrix
     */
    public function getRightMatrix(SecureUserInterface $user, array $roles)
    {
        $rightModel = $this->getModelFactory()->getModel('Core\SecurityBundle\Entity\Right');
        $rights = $rightModel->getQueryBuilder()
            ->where('u.user = :usr')
            ->orWhere('u.role in (:role)')
            ->setParameter('usr', $user)
            ->setParameter('role', $roles)
            ->getQuery()
            ->getResult();

        $rightSet = [];
        foreach ($rights as $right) {
            $item = $this->createRightSetItem($right);
            if (isset($rightSet[$item['objectidentity_name']])) {
                $rightSetItem = $rightSet[$item['objectidentity_name']];
                if ($rightSetItem['right_viewRight'] === true) {
                    $rightSet[$item['objectidentity_name']]['right_viewRight'] = true;
                }
                if ($rightSetItem['right_editRight'] === true) {
                    $rightSet[$item['objectidentity_name']]['right_editRight'] = true;
                }
                if ($rightSetItem['right_masterRight']['right_masterRight'] === true) {
                    $rightSet[$item['objectidentity_name']] = true;
                }
                if ($rightSetItem['right_scope_mask'] < $item['right_scope_mask']) {
                    $rightSet[$item['objectidentity_name']]['right_scope_mask'] = $item['right_scope_mask'];
                    $rightSet[$item['objectidentity_name']]['right_scope_id'] = $item['right_scope_id'];
                }
                continue;
            }
            $rightSet[$item['objectidentity_name']] = $item;
        }

        return $rightSet;
    }


    public function getUserRights($user)
    {
        $rights = $this->getModelFactory()->getModel('Core\SecurityBundle\Entity\Right')->findBy(['user' => $user]);
        $rightSet = [];
        foreach ($rights as $right) {
            $rightSet[] = $this->createRightSetItem($right);
        }
        return $rightSet;
    }

    public function getRoleRights($role)
    {
        $rights = $this->getModelFactory()->getModel('Core\SecurityBundle\Entity\Right')->findBy(['role' => $role]);
        dump($rights);
        $rightSet = [];
        foreach ($rights as $right) {
            $rightSet[] = $this->createRightSetItem($right);
        }
        return $rightSet;
    }

    protected function createRightSetItem(RightEntity $right)
    {
        $rightSetItem = [];
        $rightSetItem["objectidentity_id"] = $right->getObjectIdentity()->getId();
        $rightSetItem["objectidentity_name"] = $right->getObjectIdentity()->getName();
        $rightSetItem["objectidentity_displayName"] = $right->getObjectIdentity()->getDisplayName();
        $rightSetItem["right_id"] = $right->getId();
        $rightSetItem["right_viewRight"] = $right->getViewRight();
        $rightSetItem["right_editRight"] = $right->getEditRight();
        $rightSetItem["right_masterRight"] = $right->getMasterRight();
        $rightSetItem["right_scope_id"] = $right->getScope()->getId();
        $rightSetItem["right_scope_mask"] = $right->getScope()->getMask();

        return $rightSetItem;
    }
    
    /**
     * @depreceated use getRoleRights
     */
    public function getGroupRights($group)
    {
        $qb = $this->getManager()->createQuery('SELECT objectidentity,right FROM Core\SecurityBundle\Entity\ObjectIdentity objectidentity LEFT JOIN Core\SecurityBundle\Entity\Right right WITH right.objectidentity = objectidentity  and right.group = :group WHERE objectidentity.objectIdentityType = 2')
                ->setParameter('group', $group);
        $result = $qb->getScalarResult();
        return $result;
    }

    protected $enitityClassNames = array();
    protected $enitityClassNamesFromDb = array();
    protected $allMetadata;
    protected $loadedObjectIdent = array();

    public function updateObjectIdentityList()
    {
        $this->getObjectIdentityNames();
        $this->getObjectIdentityNamesFromDb();
        foreach ($this->enitityClassNames as $enitityClassName) {
            if (!array_search($enitityClassName, $this->enitityClassNamesFromDb)) {
                $entityObject = $this->getEntity();
                $entityObject->setName($enitityClassName);
                $entityObject->setIsBusinessObject(true);
                $entityObject->setParent($this->findOneById(1));
                $objectIdentityType = $this->getManager()->getReference("Core\SecurityBundle\Entity\ObjectIdentityType", 3);
                $objectIdentityType->getId();
                $entityObject->setObjectIdentityType($objectIdentityType);
                $this->create($entityObject, true);
                $this->flush();
                $this->loadedObjectIdent[] = $enitityClassName;
            }
        }

        $this->flush();

        return $this->loadedObjectIdent;
    }

    public function getObjectIdentityNames()
    {
        $this->allMetadata = $this->manager->getMetadataFactory()->getAllMetadata();
        foreach ($this->allMetadata as $m) {
            $nameArr = explode('\\', $m->getName());
            if ($nameArr[0] == 'TMSolution') {
                $this->enitityClassNames[] = $m->getName();
            }
        }

        return $this->enitityClassNames;
    }

    public function getObjectIdentityNamesFromDb($onlyBusinessClasses = false)
    {
        if ($onlyBusinessClasses) {
            $query = $this->getManager()->createQuery("SELECT s.name FROM Core\SecurityBundle\Entity\ObjectIdentity s WHERE s.isBusinessObject=true ORDER BY s.name ASC ");
        } else {
            $query = $this->getManager()->createQuery("SELECT s.name FROM Core\SecurityBundle\Entity\ObjectIdentity s ORDER BY s.name ASC ");
        }




        $result = $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);


        $this->enitityClassNamesFromDb = array_map(function ($value) {
            return $value['name'];
        }, $result);


        return $this->enitityClassNamesFromDb;
    }
}
