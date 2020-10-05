<?php

namespace Test\Register;

use Test\Common\AbstractPs15Selenium;

/**
 * @requires prestashop15basic
 * @group prestashop15register
 */
class ClearpayPs15RegisterTest extends AbstractPs15Selenium
{
    /**
     * @throws \Exception
     */
    public function testRegisterAndLogin()
    {
        $this->createAccount();
        $this->loginToFrontend();
        $this->quit();
    }
}
