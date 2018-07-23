<?php

namespace Test\Buy;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs15Selenium;

/**
 * @requires prestashop15install
 * @requires prestashop15register
 *
 * @group prestashop15buy
 */
class PaylaterPs15BuyTest extends AbstractPs15Selenium
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
