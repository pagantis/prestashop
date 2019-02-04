<?php

namespace Test\Basic;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs16Selenium;

/**
 * @group prestashop16basic
 */
class BasicPs16Test extends AbstractPs16Selenium
{
    /**
     * Const title
     */
    const TITLE = 'PrestaShop';

    /**
     * @throws \Exception
     */
    public function testTitlePrestashop16()
    {
        $this->webDriver->get(self::PS16URL);
        $condition = WebDriverExpectedCondition::titleContains(self::TITLE);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function testBackOfficeTitlePrestashop16()
    {
        $this->webDriver->get(self::PS16URL.self::BACKOFFICE_FOLDER);
        $condition = WebDriverExpectedCondition::titleContains(self::TITLE);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
        $this->quit();
    }
}
