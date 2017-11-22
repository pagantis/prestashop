<?php

namespace Test\Selenium\Basic;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class BasicPs17Test
 * @package Test\Selenium\Basic
 *
 * @group prestashop17basic
 */
class BasicPs17Test extends PaylaterPrestashopTest
{
    /**
     * Const title
     */
    const TITLE = 'PrestaShop';

    /**
     * testTitlePrestashop17
     */
    public function testTitlePrestashop17()
    {
        $this->webDriver->get(self::PS17URL);
        $condition = WebDriverExpectedCondition::titleContains(self::TITLE);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
        $this->quit();
    }

    /**
     * testBackOfficeTitlePrestashop17
     */
    public function testBackOfficeTitlePrestashop17()
    {
        $this->webDriver->get(self::PS17URL.self::BACKOFFICE_FOLDER);
        $condition = WebDriverExpectedCondition::titleContains(self::TITLE);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
        $this->quit();
    }
}
