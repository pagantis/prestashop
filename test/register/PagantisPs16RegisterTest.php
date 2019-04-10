<?php

namespace Test\Register;

use Test\Common\AbstractPs16Selenium;

/**
 * @requires prestashop16basic
 * @group prestashop16register
 */
class PagantisPs16RegisterTest extends AbstractPs16Selenium
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
