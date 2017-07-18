<?php

namespace Test\Selenium\Basic;

use Test\Selenium\PaylaterPrestashopTestCase;

/**
 * Class BasicTest
 * @package Test\Selenium\Basic
 *
 * @group ps17
 */
class BasicPs17Test extends PaylaterPrestashopTestCase
{
    /**
     * testTitlePrestashop17
     */
    public function testTitlePrestashop17()
    {
        $this->webDriver->get(self::PS17URL);
        $this->assertEquals('PrestaShop', $this->webDriver->getTitle());
    }

    /**
     * testBackOfficeTitlePrestashop17
     */
    public function testBackOfficeTitlePrestashop17()
    {
        $this->webDriver->get(self::PS17URL.self::BACKOFFICE_FOLDER);
        $this->assertContains('PrestaShop', $this->webDriver->getTitle());
    }
}
