<?php

namespace Test\HiddenProperties;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PagaMasTarde\SeleniumFormUtils\SeleniumHelper;
use Test\Common\AbstractPs15Selenium;

/**
 * @requires prestashop17install
 * @requires prestashop17register
 *
 * @group prestashop17hiddenProperties
 */
class HiddenPropertiesPs17Test extends AbstractPs15Selenium
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
