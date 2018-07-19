<?php

namespace Test\Basic;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs15Selenium;

/**
 * @group prestashop15basic
 */
class BasicPs15Test extends AbstractPs15Selenium
{
    /**
     * Const title
     */
    const TITLE = 'PrestaShop';

    /**
     * @throws \Exception
     */
    public function testTitlePrestashop15()
    {
        $this->webDriver->get(self::PS15URL);
        $condition = WebDriverExpectedCondition::titleContains(self::TITLE);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function testBackOfficeTitlePrestashop15()
    {
        $this->webDriver->get(self::PS15URL.self::BACKOFFICE_FOLDER);
        $condition = WebDriverExpectedCondition::titleContains(self::TITLE);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
        $this->quit();
    }
}
