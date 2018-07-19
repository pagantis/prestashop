<?php

namespace Test\Buy;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs16Selenium;

/**
 * @requires prestashop16install
 * @requires prestashop16register
 *
 * @group prestashop16buy
 */
class PaylaterPs16BuyTest extends AbstractPs16Selenium
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

    /**
     * Verify Paylater iframe
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function verifyPaylater()
    {
        $paylaterCheckout = WebDriverBy::className('paylater-checkout');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($paylaterCheckout);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($paylaterCheckout)->click();
        $modulePayment = $this->webDriver->findElement(WebDriverBy::id('module-paylater-payment'));
        $firstIframe = $modulePayment->findElement(WebDriverBy::tagName('iframe'));
        $condition = WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt($firstIframe);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $pmtModal = WebDriverBy::id('pmtmodal');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($pmtModal);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $iFrame = 'pmtmodal_iframe';
        $condition = WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt($iFrame);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $paymentFormElement = WebDriverBy::name('form-continue');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($paymentFormElement);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->assertContains(
            'compra',
            $this->findByClass('Form-heading1')->getText()
        );
    }
}
