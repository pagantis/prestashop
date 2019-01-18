<?php

namespace Test\Dotenv;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PagaMasTarde\SeleniumFormUtils\SeleniumHelper;
use Test\Common\AbstractPs15Selenium;

/**
 * @requires prestashop17install
 * @requires prestashop17register
 *
 * @group prestashop17dotenv
 */
class DotenvPs17Test extends AbstractPs15Selenium
{
    /**
     * @throws \Exception
     */
    public function testPmtTitleConfig()
    {
        $this->quit();
    }
}
