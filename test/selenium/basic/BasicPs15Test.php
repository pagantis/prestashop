<?php

namespace Test\Selenium\Basic;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class BasicPs15Test
 * @package Test\Selenium\Basic
 *
 * @group prestashop15basic
 */
class BasicPs15Test extends PaylaterPrestashopTest
{
    /**
     * Const title
     */
    const TITLE = 'PrestaShop';

    /**
     * testTitlePrestashop15
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
     * testBackOfficeTitlePrestashop15
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
