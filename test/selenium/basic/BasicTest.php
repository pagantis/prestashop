<?php

namespace Test\Selenium\Basic;

/**
 * Class WebTest
 * @package Test\Selenium\Basic
 */
class WebTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    /**
     * Configure selenium
     */
    protected function setUp()
    {
        $this->setBrowser('chrome');
        $this->setBrowserUrl('http://localhost:4444');
    }

    /**
     * testTitle
     */
    public function testTitle()
    {
        $this->url('http://prestashop15');
        $this->assertEquals('PrestaShop', $this->title());

        $this->url('http://prestashop16');
        $this->assertEquals('PrestaShop', $this->title());

        $this->url('http://prestashop17');
        $this->assertEquals('PrestaShop', $this->title());

        $this->url('http://prestashop18');
        $this->assertEquals('PrestaShop', $this->title());
    }

    /**
     * testTitle
     */
    public function testBackofficeTitle()
    {
        $this->url('http://prestashop15/adminTest');
        $this->assertContains('Administration panel', $this->title());

        $this->url('http://prestashop16/adminTest');
        $this->assertContains('Administration panel', $this->title());

        $this->url('http://prestashop17/adminTest');
        $this->assertContains('PrestaShop', $this->title());

        $this->url('http://prestashop18/adminTest');
        $this->assertContains('PrestaShop', $this->title());
    }
}
