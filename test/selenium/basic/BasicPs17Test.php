<?php

namespace Test\Selenium\Basic;

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
        $this->assertEquals('PrestaShop', $this->webDriver->getTitle());

        $this->quit();
    }

    /**
     * testBackOfficeTitlePrestashop17
     */
    public function testBackOfficeTitlePrestashop17()
    {
        $this->webDriver->get(self::PS17URL.self::BACKOFFICE_FOLDER);
        $this->assertContains('PrestaShop', $this->webDriver->getTitle());

        $this->quit();
    }
}
