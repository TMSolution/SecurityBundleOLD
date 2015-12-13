<?php

namespace Core\SecurityBundle\Context;

/**
 * Token naming strategy
 */
interface RightTokenStrategyInterface
{
    /**
     * @return string token name
     */
    public function getTokenName();
}
