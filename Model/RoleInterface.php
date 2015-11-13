<?php
namespace Core\SecurityBundle\Model;

interface RoleInterface
{    
    public function addRole($role);
    public function hasRole($name);    
    public function removeRole($role);
    public function getRoles();
    
}
