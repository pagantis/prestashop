<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs16BuyTest
 * @package Test\Selenium\Basic
 *
 * @group prestashop16buy
 */
class PaylaterPs16BuyTest extends PaylaterPrestashopTest
{
    /**
     * Test to buy
     */
    public function testBuy()
    {
        $this->login();
        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout();
        $this->verifyPaylater();
        $this->quit();
    }

    /**
     * Add Product
     */
    public function goToProduct()
    {
        $this->findById('header_logo')->click();
        $featuredProductCenterSearch = WebDriverBy::id('center_column');
        $condition                   = WebDriverExpectedCondition::visibilityOfElementLocated(
            $featuredProductCenterSearch
        );
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->moveToElementAndClick($this->findByClass('replace-2x'));
        $available = WebDriverBy::id('availability_statut');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($available);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
    }

    /**
     * Add Product
     */
    public function addProduct()
    {
        $this->findById('buy_block')->submit();
        $cartTitle = WebDriverBy::id('cart_title');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($cartTitle);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * Go to checkout
     */
    public function goToCheckout()
    {
        $this->findByClass('shopping_cart')->click();
        $checkoutButton = WebDriverBy::className('standard-checkout');
        $condition      = WebDriverExpectedCondition::visibilityOfElementLocated($checkoutButton);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->webDriver->findElement($checkoutButton)->click();
        try {
            $addressInputSearch = WebDriverBy::id('firstname');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($addressInputSearch);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
            $this->findById('company')->clear()->sendKeys($this->configuration['company']);
            $this->findById('address1')->clear()->sendKeys('av.diagonal 579');
            $this->findById('postcode')->clear()->sendKeys($this->configuration['zip']);
            $this->findById('city')->clear()->sendKeys($this->configuration['city']);
            $this->findById('phone')->clear()->sendKeys($this->configuration['phone']);
            $this->findById('phone_mobile')->clear()->sendKeys($this->configuration['phone']);
            $this->findById('dni')->clear()->sendKeys($this->configuration['dni']);
            $this->findById('id_state')->sendKeys('Barcelona');
            $this->findById('submitAddress')->click();
            $processAddress = WebDriverBy::name('processAddress');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processAddress);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
        } catch (\Exception $exception) {
            $processAddress = WebDriverBy::name('processAddress');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processAddress);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
        }
        $this->webDriver->findElement($processAddress)->click();
        $processCarrier = WebDriverBy::name('processCarrier');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processCarrier);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findById('cgv')->click();
        $this->webDriver->findElement($processCarrier)->click();
        $hookPayment = WebDriverBy::id('HOOK_PAYMENT');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($hookPayment);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        //$pmtSimulator = WebDriverBy::className('PmtSimulator');
        //$condition = WebDriverExpectedCondition::visibilityOfElementLocated($pmtSimulator);
        //$this->waitUntil($condition);
        //$this->assertTrue((bool) $condition);
    }

    /**
     * Verify paylater
     */
    public function verifyPaylater()
    {
        $paylaterCheckout = WebDriverBy::className('paylater-checkout');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($paylaterCheckout);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($paylaterCheckout)->click();
        $iFrame = 'iframe-pagantis';
        $condition = WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt($iFrame);
        $this->webDriver->wait(30, 50)->until($condition);
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

    /**
     * LOGIN
     */
    public function login()
    {
        $this->webDriver->get(self::PS16URL);
        $login = WebDriverBy::className('header_user_info');
        $condition = WebDriverExpectedCondition::elementToBeClickable($login);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($login)->click();
        $loginSubmit = WebDriverBy::id('SubmitLogin');
        $condition = WebDriverExpectedCondition::elementToBeClickable($loginSubmit);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($loginSubmit)->click();
        $this->findById('email')->sendKeys($this->configuration['email']);
        $this->findById('passwd')->sendKeys($this->configuration['password']);
        $this->findById('SubmitLogin')->click();
    }
}
