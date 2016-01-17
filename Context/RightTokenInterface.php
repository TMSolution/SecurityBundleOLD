<?php

namespace Core\SecurityBundle\Context;

interface RightTokenInterface
{
    public function getName();
    public function equals(RightTokenInterface $token);
    public function isWhiteListed();
}
