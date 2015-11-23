<?php

namespace Core\SecurityBundle\Model;

interface SecureUserInterface extends RoleInterface
{
    public function getId();
}
