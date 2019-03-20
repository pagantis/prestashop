<?php

namespace Test\Register;

use Test\Common\AbstractPs17Selenium;

/**
 * @requires prestashop17basic
 * @group prestashop17register
 */
class PagantisPs17RegisterTest extends AbstractPs17Selenium
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
