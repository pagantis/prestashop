<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs17BuyTest
 * @package Test\Selenium\Basic
 *
 * @group prestashop17buy
 */
class PaylaterPs17BuyTest extends PaylaterPrestashopTest
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
        $this->findById('_desktop_logo')->click();
        $featuredProductCenterSearch = WebDriverBy::className('products');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($featuredProductCenterSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->findByClass('product-description')->click();
        $available = WebDriverBy::id('product-availability');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($available);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
    }

    /**
     * Add Product
     */
    public function addProduct()
    {
        $this->findByClass('add-to-cart')->click();
        $cartTitle = WebDriverBy::className('cart-products-count');
        /** @var WebDriverExpectedCondition $condition */
        $condition = WebDriverExpectedCondition::textToBePresentInElement($cartTitle, '(1)');
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->executeScript('document.querySelector(\'.close\').click();');
    }

    /**
     * Go to checkout
     */
    public function goToCheckout()
    {
        sleep(1);
        $cartButton = WebDriverBy::id('_desktop_cart');
        $condition = WebDriverExpectedCondition::elementToBeClickable($cartButton);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($cartButton)->click();
        $checkoutButton = WebDriverBy::className('cart-detailed-actions');
        $checkoutButton = $checkoutButton->className('btn-primary');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($checkoutButton);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->webDriver->findElement($checkoutButton)->click();
        try {
            $addressInputSearch = WebDriverBy::name('firstname');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($addressInputSearch);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
            $this->findByName('company')->clear()->sendKeys($this->configuration['company']);
            $this->findByName('address1')->clear()->sendKeys('av.diagonal 579');
            $this->findByName('postcode')->clear()->sendKeys($this->configuration['zip']);
            $this->findByName('city')->clear()->sendKeys($this->configuration['city']);
            $this->findByName('phone')->clear()->sendKeys($this->configuration['phone']);
            $this->findById('delivery-address')->findElement(WebDriverBy::name('confirm-addresses'))->click();
            $processAddress = WebDriverBy::name('confirmDeliveryOption');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processAddress);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
        } catch (\Exception $exception) {
            $this->findByName('confirm-addresses')->click();
            $processAddress = WebDriverBy::name('confirmDeliveryOption');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processAddress);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
        }
        $this->webDriver->findElement($processAddress)->click();
        $processCarrier = WebDriverBy::id('payment-confirmation');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processCarrier);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($processCarrier)->click();
        $hookPayment = WebDriverBy::id('checkout-payment-step');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($hookPayment);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $paylaterOption = WebDriverBy::cssSelector('[for=payment-option-3]');
        $condition = WebDriverExpectedCondition::elementToBeClickable($paylaterOption);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($paylaterOption)->click();
        $pmtSimulator = WebDriverBy::className('PmtSimulator');
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($pmtSimulator);
        $this->waitUntil($condition);
        $this->findById('conditions_to_approve[terms-and-conditions]')->click();
        $this->findById('payment-confirmation')->click();
    }

    /**
     * Verify paylater
     */
    public function verifyPaylater()
    {
        $iFrame = 'iframe-pagantis';
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

    /**
     * LOGIN
     */
    public function login()
    {
        $this->webDriver->get(self::PS17URL);
        $login = WebDriverBy::className('user-info');
        $condition = WebDriverExpectedCondition::elementToBeClickable($login);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($login)->click();
        $loginForm = WebDriverBy::id('login-form');
        $condition = WebDriverExpectedCondition::elementToBeClickable($loginForm);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findByName('email')->sendKeys($this->configuration['email']);
        $this->findByName('password')->sendKeys($this->configuration['password']);
        $this->webDriver->findElement($loginForm)->submit();
    }
}
