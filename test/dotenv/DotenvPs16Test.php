<?php

namespace Test\Dotenv;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PagaMasTarde\SeleniumFormUtils\SeleniumHelper;
use Test\Common\AbstractPs15Selenium;

/**
 * @requires prestashop16install
 * @requires prestashop16register
 *
 * @group prestashop16dotenv
 */
class DotenvPs16Test extends AbstractPs15Selenium
{
    /**
     * @throws \Exception
     */
    public function testPmtTitleConfig()
    {
        $this->quit();
    }
}
