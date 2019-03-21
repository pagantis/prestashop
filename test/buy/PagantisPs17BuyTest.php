<?php

namespace Test\Buy;

use Test\Common\AbstractPs17Selenium;

/**
 * @requires prestashop17install
 * @requires prestashop17register
 *
 * @group prestashop17buy
 */
class PagantisPs17BuyTest extends AbstractPs17Selenium
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
        $this->verifyPagantis();
        $this->quit();
    }
}
