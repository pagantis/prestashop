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
        $this->addProductAndGoToCheckout();
        $this->quit();
    }

    public function addProductAndGoToCheckout()
    {

        $this->findByClass('sf-with-ul')->click();
        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className('ajax_add_to_cart_button'))
        );
        $this->findByClass('ajax_add_to_cart_button')->click();
        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className('continue'))
        );
        $this->findByClass('continue')->click();

        sleep(2);

        $this->findByClass('shopping_cart')->findElement(WebDriverBy::cssSelector('a'))->click();
        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('standard-checkout')
            )
        );
        $this->findByClass('standard-checkout')->click();

        try {
            $this->findByClass('address_update')->findElement(WebDriverBy::cssSelector('a'))->click();
        } catch (\Exception $exception) {
        }

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('company')
            )
        );

        $this->findById('company')->clear()->sendKeys($this->configuration['company']);
        $this->findById('address1')->clear()->sendKeys('av.diagonal 579');
        $this->findById('postcode')->clear()->sendKeys($this->configuration['zip']);
        $this->findById('city')->clear()->sendKeys($this->configuration['city']);
        $this->findById('phone')->clear()->sendKeys($this->configuration['phone']);
        $this->findById('phone_mobile')->clear()->sendKeys($this->configuration['phone']);
        $this->findById('dni')->clear()->sendKeys($this->configuration['dni']);
        $this->findById('id_state')->findElement(
            WebDriverBy::cssSelector("option[value='322']")
        )->click();

        $this->findById('submitAddress')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::name('processAddress')
            )
        );

        $this->findByName('processAddress')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::name('processCarrier')
            )
        );

        $this->findById('cgv')->click();
        $this->findByName('processCarrier')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('paylater-checkout')
            )
        );

        //PAYMENT SHOWN
        $this->assertContains(
            'Financiar con Paga+Tarde',
            $this->findByClass('paylater-checkout')->getText()
        );

        $this->findByClass('paylater-checkout')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt('iframe-pagantis')
        );

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::name('form-continue')
            )
        );

        $this->assertContains(
            'Financia tu compra de',
            $this->findByClass('Form-heading1')->getText()
        );

        //PAYMENT METHOD WORKS!! YUHUUUUU
        sleep(5);
    }

    /**
     * LOGIN
     */
    public function login()
    {
        $this->webDriver->get(self::PS16URL);
        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('header_user_info')
            )
        );

        $this->findByClass('header_user_info')->click();
        $this->findByName('email')->sendKeys($this->configuration['email']);
        $this->findByName('passwd')->sendKeys($this->configuration['password']);
        $this->findById('SubmitLogin')->click();
    }
}
