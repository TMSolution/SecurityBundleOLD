<?php

namespace Core\SecurityBundle\Context;

interface RightTokenInterface
{
    public function getName();
    public function isEmpty();
    public function equals(RightTokenInterface $token);
}
