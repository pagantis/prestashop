<?php

namespace Test\HiddenProperties;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PagaMasTarde\SeleniumFormUtils\SeleniumHelper;
use Test\Common\AbstractPs15Selenium;

/**
 * @requires prestashop16install
 * @requires prestashop16register
 *
 * @group prestashop16hiddenProperties
 */
class HiddenPropertiesPs16Test extends AbstractPs15Selenium
{
    use HiddenPropertiesPsTrait;

    /**
     * @throws \Exception
     */
    public function testPmtTitleConfig()
    {
        $this->quit();
    }
}
