<?php

namespace Core\SecurityBundle\Model;

use Core\ModelBundle\Model\Model as BaseModel;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity as SecurityObjectIdentity;

class ObjectIdentity extends BaseModel {

    /**
     * Get all resources with identity 'link'
     * 
     * @return array
     */
    public function getLinks() {

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

    protected function checkExecuteImmediately($executeImmediately) {
        if (false == $executeImmediately) {
            throw new \InvalidArgumentException('Security operations must be save immediately,  $executeImmediately parameter must be set as true');
        }
    }

    protected function createClassIdentity($entityObject) {
        return new SecurityObjectIdentity($entityObject->getName(), $entityObject->getObjectIdentityType()->getName());
    }

    protected function createACL($entityObject) {
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

    protected function updateACL($entityObject) {


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

    public function create($entityObject, $executeImmediately = false, $logOperation = false, $checkRights = true) {

        $this->checkExecuteImmediately($executeImmediately);
        parent::create($entityObject, false, $logOperation, $checkRights);
        $this->createACL($entityObject);
        if ($executeImmediately == true) {
            $this->flush();
        }
    }

    public function createEntities(ArrayCollection $arrayCollection, $executeImmediately = false, $checkRights = true) {
        $this->checkExecuteImmediately($executeImmediately);
        parent::createEntities($arrayCollection, $executeImmediately, $checkRights);
        foreach ($arrayCollection as $entityObject) {
            $this->createACL($entityObject);
        }
    }

    public function delete($entityObject, $executeImmediately = false, $logOperation = false, $checkRights = true) {
        $this->checkExecuteImmediately($executeImmediately);
        parent::delete($entityObject, $executeImmediately, $logOperation, $checkRights);
        //@todo delete object identity
    }

    public function update($entityObject, $executeImmediately = true, $logOperation = false, $checkRights = true) {
        $this->checkExecuteImmediately($executeImmediately);
        parent::update($entityObject, $executeImmediately, $logOperation, $checkRights);
        $this->updateACL($entityObject);
    }

    public function synchrozieObjectIdentity() {
        foreach ($this->getRepository()->findBy(array(), array('parent' => 'asc')) as $entityObject) {
            $this->createACL($entityObject);
        }
    }

    public function getModules() {
        return $this->findBy(['objectIdentityType' => 2]);
    }

    public function getUserRights($user) {

        $qb = $this->getManager()->createQuery('SELECT objectidentity,right FROM Core\SecurityBundle\Entity\ObjectIdentity objectidentity LEFT JOIN Core\SecurityBundle\Entity\Right right WITH right.objectidentity = objectidentity and right.user = :user WHERE objectidentity.objectIdentityType = 5')
                ->setParameter('user', $user);
        $result = $qb->getScalarResult();


        return $result;
    }

    public function getGroupRights($group) {
        $qb = $this->getManager()->createQuery('SELECT objectidentity,right FROM Core\SecurityBundle\Entity\ObjectIdentity objectidentity LEFT JOIN Core\SecurityBundle\Entity\Right right WITH right.objectidentity = objectidentity  and right.group = :group WHERE objectidentity.objectIdentityType = 2')
                ->setParameter('group', $group);
        $result = $qb->getScalarResult();
        return $result;
    }

    protected $enitityClassNames = Array();
    protected $enitityClassNamesFromDb = Array();
    protected $allMetadata;
    protected $loadedObjectIdent = Array();

    public function updateObjectIdentityList() {
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

    public function getObjectIdentityNames() {

        $this->allMetadata = $this->manager->getMetadataFactory()->getAllMetadata();
        foreach ($this->allMetadata as $m) {
            $nameArr = explode('\\', $m->getName());
            if ($nameArr[0] == 'TMSolution') {
                $this->enitityClassNames[] = $m->getName();
            }
        }

        return $this->enitityClassNames;
    }

    public function getObjectIdentityNamesFromDb($onlyBusinessClasses = false) {


        if ($onlyBusinessClasses) {
            $query = $this->getManager()->createQuery("SELECT s.name FROM Core\SecurityBundle\Entity\ObjectIdentity s WHERE s.isBusinessObject=true ORDER BY s.name ASC ");
        } else {
            $query = $this->getManager()->createQuery("SELECT s.name FROM Core\SecurityBundle\Entity\ObjectIdentity s ORDER BY s.name ASC ");
        }




        $result = $query->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);


        $this->enitityClassNamesFromDb = array_map(function($value) {
            return $value['name'];
        }, $result);


        return $this->enitityClassNamesFromDb;
    }

}
