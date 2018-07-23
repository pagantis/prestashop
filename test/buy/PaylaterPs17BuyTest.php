<?php

namespace Test\Buy;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs17Selenium;

/**
 * @requires prestashop17install
 * @requires prestashop17register
 *
 * @group prestashop17buy
 */
class PaylaterPs17BuyTest extends AbstractPs17Selenium
{
    /**
     * @throws  \Exception
     */
    public function testBuy()
    {
        $this->loginToFrontend();
        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout();
        $this->verifyPaylater();
        $this->verifyUTF8();
        $this->quit();
    }
}
