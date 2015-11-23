<?php

namespace Core\SecurityBundle\Model;

use Core\ModelBundle\Model\Model as BaseModel;
use \Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity as SecurityObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Core\SecurityBundle\Context\RightTokenInterface;
use FOS\UserBundle\Model\UserInterface;

class Right extends BaseModel
{

    public function findRight(UserInterface $user, RightTokenInterface $token) {

        try {
            $objectIdentity =
                $this->getModelFactory()
                    ->getModel('Core\SecurityBundle\Entity\ObjectIdentity')
                    ->findOneBy(['name' => $token->getName()]);
        } catch(EntityNotFoundException $ex) {
            return [];
        }

        return $this->getQueryBuilder('u')
            ->where('u.objectidentity = :oi')
            ->andWhere('u.user = :usr or u.role in (:roles)')
            ->setParameter('usr', $user)
            ->setParameter('oi', $objectIdentity)
            ->setParameter('roles', $user->getRoles())
            ->getQuery()
            ->getResult();
        
    }
    
    public function createUserRights($user, $records)
    {

        $modelFactory = $this->container->get("model_factory");
        $objectIdentityModel = $modelFactory->getModel('Core\\SecurityBundle\\Entity\\ObjectIdentity');
        $scopeModel = $modelFactory->getModel('Core\\SecurityBundle\\Entity\\Scope');
        $arrayCollection = new ArrayCollection();
        foreach ($records as $record) {
            
            $objectIdentity = $objectIdentityModel->findOneBy(['id' => $record['module_id'], 'objectIdentityType' => 5]);            
            if ($objectIdentity) {
                $right = $this->getEntity();
                $right->setScope($scopeModel->findOneById($record['scope_id']));
                $right->setObjectIdentity($objectIdentity);
                $right->setUser($user);
                $maskBuilder = $this->setRights($right, $record);
                $arrayCollection[] = $right;

                $this->insertAceForUser($right, $user, $maskBuilder);
            }
        }

        $this->createEntities($arrayCollection, true);
    }

    public function createRoleRights($role, $records)
    {

        $modelFactory = $this->container->get("model_factory");
        $objectIdentityModel = $modelFactory->getModel('Core\\SecurityBundle\\Entity\\ObjectIdentity');
        $scopeModel = $modelFactory->getModel('Core\\SecurityBundle\\Entity\\Scope');
        $arrayCollection = new ArrayCollection();
        foreach ($records as $record) {

            $objectIdentity = $objectIdentityModel->findOneBy(['id' => $record['module_id'], 'objectIdentityType' => 5]);
            if ($objectIdentity) {
                $role = $this->getEntity();
                $right->setScope($scopeModel->findOneById($record['scope_id']));
                $role->setObjectIdentity($objectIdentity);
                $role->setRole($role);
                $maskBuilder = $this->setRights($role, $record);
                $arrayCollection[] = $role;
                $this->insertAceForRole($role, $role, $maskBuilder);
            }
        }

        $this->createEntities($arrayCollection, true);
    }

    public function updateUserRights($user, $records)
    {

        $modelFactory = $this->container->get("model_factory");
        $objectIdentityModel = $modelFactory->getModel('Core\\SecurityBundle\\Entity\\ObjectIdentity');
        $scopeModel = $modelFactory->getModel('Core\\SecurityBundle\\Entity\\Scope');
        $arrayCollection = new ArrayCollection();
        foreach ($records as $record) {
            $right = $this->getRepository()->findOneBy(['user' => $user->getId(), 'objectidentity' => $record['module_id']]);
            if ($right == null) {
                $objectIdentity = $objectIdentityModel->findOneById($record['module_id']);
                $right = $this->getEntity();
                $right->setObjectIdentity($objectIdentity);
                $right->setUser($user);
                $right->setScope($scopeModel->findOneById($record['scope_id']));
                $arrayCollection[] = $right;
            } else {
                $right->setUser($user);
                $right->setScope($scopeModel->findOneById($record['scope_id']));
                $this->update($right);
            }
            $maskBuilder = $this->setRights($right, $record);
            $this->insertAceForUser($right, $user, $maskBuilder);
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
      protected function insertClassAce($scope, $businessObjectIdentity, $maskArray)
      {
      $securityIdentity = new RoleSecurityIdentity($scope);
      $acl = $this->findAcl($businessObjectIdentity);
      //wywal wszystko wczesniej
      foreach ($maskArray as $mask) {
      $acl->insertClassAce($securityIdentity, $maskArray);
      }
      } */

    public function updateRoleRights($role, $records)
    {

        $modelFactory = $this->container->get("model_factory");
        $objectIdentityModel = $modelFactory->getModel('Core\\SecurityBundle\\Entity\\ObjectIdentity');
        $scopeModel = $modelFactory->getModel('Core\\SecurityBundle\\Entity\\Scope');
        $arrayCollection = new ArrayCollection();
        foreach ($records as $record) {
            $right = $this->getRepository()->findOneBy(['role' => $role->getId(), 'objectidentity' => $record['module_id']]);
            if (empty($right)) {
                $objectIdentity = $objectIdentityModel->findOneById($record['module_id']);
                $right = $this->getEntity();
                $right->setObjectIdentity($objectIdentity);
                $right->setRole($role);
                $right->setScope($scopeModel->findOneById($record['scope_id']));
                $maskBuilder = $this->setRights($right, $record);
                $arrayCollection[] = $right;
            } else {
                $right->setRole($role);
                $right->setScope($scopeModel->findOneById($record['scope_id']));
                $maskBuilder = $this->setRights($right, $record);
                $this->update($right);
            }
            $this->insertAceForRole($right, $role, $maskBuilder);
        }

        $this->createEntities($arrayCollection);
        $this->flush();
    }

    protected function insertAceForRole($entity, $role, $maskBuilder)
    {

        $aclProvider = $this->container->get('security.acl.provider');
        $securityIdentity = new RoleSecurityIdentity($role->getRole());
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
//        dump("SECURITY IDENTITY");
//        dump($securityIdentity);
        $acl = $this->findAcl($entity->getObjectIdentity());
//        dump("ACL ID");
//        dump($acl->getId());
        if (!$this->updateAce($acl, $securityIdentity, $maskBuilder->get())) {

            $acl->insertObjectAce($securityIdentity, $maskBuilder->get());
        }                
//        $acl->insertObjectAce($securityIdentity, $maskBuilder->get());

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
        $objectName = $this->container->get("classmapperservice")->getEntityClass($masterRequest->attributes->get('entityName'), $masterRequest->getLocale());

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
