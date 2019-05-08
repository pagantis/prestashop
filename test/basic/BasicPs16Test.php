<?php

namespace Test\Basic;

use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs16Selenium;

/**
 * @group prestashop16basic
 */
class BasicPs16Test extends AbstractPs16Selenium
{
    /**
     * Const title
     */
    const TITLE = 'PrestaShop';

    /**
     * @throws \Exception
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
     * @throws \Exception
     */
    public function testBackOfficeTitlePrestashop16()
    {
        $this->webDriver->get(self::PS16URL.self::BACKOFFICE_FOLDER);
        $condition = WebDriverExpectedCondition::titleContains(self::TITLE);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function testPStotalAmount()
    {
        $this->assertEquals('500', $this->getPSTotalAmount(5));
        $this->assertEquals('55500', $this->getPSTotalAmount(555));
        $this->assertEquals('55555', $this->getPSTotalAmount(555.55));
        $this->assertEquals('500', $this->getPSTotalAmount('5'));
        $this->assertEquals('55555', $this->getPSTotalAmount('555.55'));
        $this->assertEquals('500', $this->getPSTotalAmount((float) 5));
        $this->assertEquals('55555', $this->getPSTotalAmount((float) 555.55));
        $this->assertEquals('500', $this->getPSTotalAmount((int) 5));
        $this->assertEquals('55500', $this->getPSTotalAmount((int) 555.55));
        $this->quit();
    }

    /**
     * @param null $amount
     * @return mixed
     */
    public function getPSTotalAmount($amount = null)
    {
        $totalAmount = (string)(100 * $amount);
        return explode('.', explode(',', $totalAmount)[0])[0];
    }
}
