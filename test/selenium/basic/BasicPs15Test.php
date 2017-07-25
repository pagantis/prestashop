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
     * testTitlePrestashop15
     */
    public function testTitlePrestashop15()
    {
        $this->webDriver->get(self::PS15URL);

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                'PrestaShop'
            )
        );

        $this->assertEquals('PrestaShop', $this->webDriver->getTitle());
        $this->quit();
    }

    /**
     * testBackOfficeTitlePrestashop15
     */
    public function testBackOfficeTitlePrestashop15()
    {
        $this->webDriver->get(self::PS15URL.self::BACKOFFICE_FOLDER);

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::titleContains(
                'PrestaShop'
            )
        );

        $this->assertContains('PrestaShop', $this->webDriver->getTitle());
        $this->quit();
    }
}
