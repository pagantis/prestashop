<?php

namespace Test\Selenium\Basic;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class BasicPs16Test
 * @package Test\Selenium\Basic
 *
 * @group prestashop16basic
 */
class BasicPs16Test extends PaylaterPrestashopTest
{
    /**
     * Const title
     */
    const TITLE = 'PrestaShop';

    /**
     * testTitlePrestashop16
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
     * testBackOfficeTitlePrestashop16
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
