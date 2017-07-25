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
     * testTitlePrestashop16
     */
    public function testTitlePrestashop16()
    {
        $this->webDriver->get(self::PS16URL);

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                'PrestaShop'
            )
        );

        $this->assertEquals('PrestaShop', $this->webDriver->getTitle());
        $this->quit();
    }

    /**
     * testBackOfficeTitlePrestashop16
     */
    public function testBackOfficeTitlePrestashop16()
    {
        $this->webDriver->get(self::PS16URL.self::BACKOFFICE_FOLDER);

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                'PrestaShop'
            )
        );

        $this->assertContains('PrestaShop', $this->webDriver->getTitle());
        $this->quit();
    }
}
