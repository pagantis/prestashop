<?php

namespace Test\Selenium\Basic;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class BasicTest
 * @package Test\Selenium\Basic
 *
 * @group prestashop17
 * @group basic
 */
class BasicPs17Test extends PaylaterPrestashopTest
{
    /**
     * testTitlePrestashop17
     */
    public function testTitlePrestashop17()
    {
        $this->webDriver->get(self::PS17URL);

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                'PrestaShop'
            )
        );

        $this->assertEquals('PrestaShop', $this->webDriver->getTitle());
        $this->quit();
    }

    /**
     * testBackOfficeTitlePrestashop17
     */
    public function testBackOfficeTitlePrestashop17()
    {
        $this->webDriver->get(self::PS17URL.self::BACKOFFICE_FOLDER);

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                'PrestaShop'
            )
        );

        $this->assertContains('PrestaShop', $this->webDriver->getTitle());
        $this->quit();
    }
}
