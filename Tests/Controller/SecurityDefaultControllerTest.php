<?php

/*
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core\SecurityBundle\Tests\Controller;

use TMSolution\TestingBundle\Functional\AppTestCase;
use TMSolution\TestingBundle\Functional\Url;

/**
 * Functional test for Core\SecurityBundle\Controller\SecurityDefaultController 
 */
class SecurityDefaultControllerTest extends AppTestCase
{
    /**
     * Function test for Core\SecurityBundle\Controller\SecurityDefaultController::unauthorizedAction
     *
     * @Url("")
     */
    public function testUnauthorizedAction()
    {
        $this->assertTrue(true);
    }
    
}
