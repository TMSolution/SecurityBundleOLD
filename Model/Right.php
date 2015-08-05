<?php

namespace Core\SecurityBundle\Model;

use Core\ModelBundle\Model\Model as BaseModel;
use \Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity as SecurityObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class Right extends BaseModel
{

    public function createUserRights($user, $records)
    {

        $modelFactory = $this->container->get("model_factory");
        $objectIdentityModel = $modelFactory->getModel('Core\SecurityBundle\Entity\ObjectIdentity');


        $arrayCollection = new ArrayCollection();
        foreach ($records as $record) {

            $objectIdentity = $objectIdentityModel->findOneBy(['id' => $record['module_id'], 'objectIdentityType' => 2]);
            if ($objectIdentity) {

                $entity = $this->getEntity();
                $entity->setObjectIdentity($objectIdentity);
                $entity->setUser($user);
                $maskBuilder = $this->setRights($entity, $record);
                $arrayCollection[] = $entity;

                $this->insertAceForUser($entity, $user, $maskBuilder);
            }
        }

        $this->createEntities($arrayCollection, true);
    }

    public function createGroupRights($group, $records)
    {

        $modelFactory = $this->container->get("model_factory");
        $objectIdentityModel = $modelFactory->getModel('Core\SecurityBundle\Entity\ObjectIdentity');


        $arrayCollection = new ArrayCollection();
        foreach ($records as $record) {

            $objectIdentity = $objectIdentityModel->findOneBy(['id' => $record['module_id'], 'objectIdentityType' => 2]);
            if ($objectIdentity) {

                $entity = $this->getEntity();
                $entity->setObjectIdentity($objectIdentity);
                $entity->setGroup($group);
                $maskBuilder = $this->setRights($entity, $record);
                $arrayCollection[] = $entity;
                $this->insertAceForRole($entity, $group, $maskBuilder);
            }
        }

        $this->createEntities($arrayCollection, true);
    }

    public function updateUserRights($user, $records)
    {

        $modelFactory = $this->container->get("model_factory");
        $objectIdentityModel = $modelFactory->getModel('Core\SecurityBundle\Entity\ObjectIdentity');


        $arrayCollection = new ArrayCollection();
        foreach ($records as $record) {

            $entity = $this->getRepository()->findOneBy(['user' => $user->getId(), 'objectidentity' => $record['module_id']]);

            if (empty($entity)) {
                $objectIdentity = $objectIdentityModel->findOneById($record['module_id']);
                $entity = $this->getEntity();

                $entity->setObjectIdentity($objectIdentity);
                $entity->setUser($user);
                $maskBuilder = $this->setRights($entity, $record);
                $arrayCollection[] = $entity;
            } else {

                $entity->setUser($user);
                $maskBuilder = $this->setRights($entity, $record);
                $this->update($entity);
            }

            $this->insertAceForUser($entity, $user, $maskBuilder);
        }



        $this->createEntities($arrayCollection);
        $this->flush();
    }

    protected function setRights($entity, $record)
    {

        $maskBuilder = new MaskBuilder();

        if ($this->hasKey('viewRight', $record)) {
            $entity->setViewRight(true);
            $maskBuilder->add(MaskBuilder::MASK_VIEW);
        } else {
            $entity->setViewRight(false);
            $maskBuilder->remove(MaskBuilder::MASK_VIEW);
        }

        if ($this->hasKey('editRight', $record)) {
            $entity->setViewRight(true);
            $entity->setEditRight(true);
            $maskBuilder->add(MaskBuilder::MASK_VIEW);
            $maskBuilder->add(MaskBuilder::MASK_EDIT);
        } else {
            $entity->setEditRight(false);
            $maskBuilder->remove(MaskBuilder::MASK_EDIT);
        }

        if ($this->hasKey('masterRight', $record)) {
            $entity->setViewRight(true);
            $entity->setEditRight(true);
            $entity->setMasterRight(true);
            $maskBuilder->add(MaskBuilder::MASK_VIEW);
            $maskBuilder->add(MaskBuilder::MASK_EDIT);
            $maskBuilder->add(MaskBuilder::MASK_MASTER);
        } else {
            $entity->setMasterRight(false);
            $maskBuilder->remove(MaskBuilder::MASK_MASTER);
        }
        return $maskBuilder;
    }

    /* public function revoke($entity, $mask = MaskBuilder::MASK_OWNER)
      {
      $acl = $this->getAcl($entity);
      $aces = $acl->getObjectAces();
      $user = $this->context->getToken()->getUser();
      $securityIdentity = UserSecurityIdentity::fromAccount($user);
      foreach ($aces as $i => $ace) {
      if ($securityIdentity->equals($ace->getSecurityIdentity())) {
      $this->revokeMask($i, $acl, $ace, $mask);
      }
      }
      $this->provider->updateAcl($acl);
      return $this;
      }

      protected function revokeMask($index, Acl $acl, Entry $ace, $mask)
      {
      $acl->updateObjectAce($index, $ace->getMask() & ~$mask);
      return $this;
      } */

    protected function findAcl($businessObjectIdentity)
    {
        if ($businessObjectIdentity) {
            $objectIdentity = new SecurityObjectIdentity($businessObjectIdentity->getName(), $businessObjectIdentity->getObjectIdentityType()->getName());

//var_dump($objectIdentity);exit;

            $aclProvider = $this->container->get('security.acl.provider');
            $acl = $aclProvider->findAcl($objectIdentity);
            return $acl;
        } else {
            throw new \Exception("BusinessObjectIdentity doesn't exists!");
        }
    }

    /*
      protected function insertClassAce($role, $businessObjectIdentity, $maskArray)
      {
      $securityIdentity = new RoleSecurityIdentity($role);
      $acl = $this->findAcl($businessObjectIdentity);
      //wywal wszystko wczesniej
      foreach ($maskArray as $mask) {
      $acl->insertClassAce($securityIdentity, $maskArray);
      }
      } */

    public function updateGroupRights($group, $records)
    {


        $modelFactory = $this->container->get("model_factory");
        $objectIdentityModel = $modelFactory->getModel('Core\SecurityBundle\Entity\ObjectIdentity');
        $arrayCollection = new ArrayCollection();
        foreach ($records as $record) {

            $entity = $this->getRepository()->findOneBy(['group' => $group->getId(), 'objectidentity' => $record['module_id']]);

            if (empty($entity)) {
                $objectIdentity = $objectIdentityModel->findOneById($record['module_id']);
                $entity = $this->getEntity();

                $entity->setObjectIdentity($objectIdentity);
                $entity->setGroup($group);
                $maskBuilder = $this->setRights($entity, $record);
                $arrayCollection[] = $entity;
            } else {
                $entity->setGroup($group);
                $maskBuilder = $this->setRights($entity, $record);
                $this->update($entity);
            }
            $this->insertAceForRole($entity, $group, $maskBuilder);
        }

        $this->createEntities($arrayCollection);
        $this->flush();
    }

    protected function insertAceForRole($entity, $group, $maskBuilder)
    {

        $aclProvider = $this->container->get('security.acl.provider');
        $securityIdentity = new RoleSecurityIdentity($group->getRole());
        $acl = $this->findAcl($entity->getObjectIdentity());

        /*
          echo '<pre>';
          \Doctrine\Common\Util\Debug::dump($acl);
          echo '</pre>';
         * 
         */


        if (!$this->updateAce($acl, $securityIdentity, $maskBuilder->get())) {

            $acl->insertObjectAce($securityIdentity, $maskBuilder->get());
        }
        $aclProvider->updateAcl($acl);
    }

    protected function insertAceForUser($entity, $user, $maskBuilder)
    {
        $aclProvider = $this->container->get('security.acl.provider');
        $securityIdentity = new UserSecurityIdentity($user->getEmail(), 'TMSolution\UserBundle\Entity\User');
        $acl = $this->findAcl($entity->getObjectIdentity());
        $acl->insertObjectAce($securityIdentity, $maskBuilder->get());
        $aclProvider->updateAcl($acl);
    }

    protected function updateAce($acl, $securityIdentity, $mask)
    {
        foreach ($acl->getObjectAces() as $index => $ace) {
            $aceSecurityId = $ace->getSecurityIdentity();
            if ($aceSecurityId->equals($securityIdentity)) {
                $acl->updateObjectAce($index, $mask);
                return true;
            }
        }
        return false;
    }

    public function check($rights)
    {
        $aclProvider = $this->container->get('security.acl.provider');
        $masterRequest = $this->container->get('request_stack')->getMasterRequest();
        $objectName = $this->container->get("classmapper")->getEntityClass($masterRequest->attributes->get('entityName'), $masterRequest->getLocale());

        $classIdentity = new SecurityObjectIdentity($objectName, 'class');
        $user = $this->container->get('security.context')->getToken()->getUser();

        $acl = $aclProvider->findAcl($classIdentity);


        $securityIdentities = [];
        foreach ($user->getRoles() as $role) {
            $securityIdentities[] = new RoleSecurityIdentity($role);
        }

        try {
            return $acl->isGranted($rights, $securityIdentities);
        } catch (\Exception $e) {
            return false;
        }
    }

}
