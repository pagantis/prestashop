<?php
namespace Test\Selenium\Basic;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs17Selenium;

/**
 * @group prestashop17basic
 */
class BasicPs17Test extends AbstractPs17Selenium
{
    /**
     * Const title
     */
    const TITLE = 'PrestaShop';

    /**
     * @throws \Exception
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
     * @throws \Exception
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
