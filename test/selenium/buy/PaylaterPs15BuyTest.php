<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs15BuyTest
 * @package Test\Selenium\Basic
 *
 * @group prestashop15buy
 */
class PaylaterPs15BuyTest extends PaylaterPrestashopTest
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
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className('first_item'))
        );
        $this->findByClass('first_item')->click();


        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className('ajax_add_to_cart_button'))
        );
        $this->findByClass('ajax_add_to_cart_button')->click();

        sleep(1);

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('shopping_cart'))
        );

        $this->findById('shopping_cart')->findElement(WebDriverBy::cssSelector('a'))->click();

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
        $this->assertTrue(true);

        $this->findByClass('paylater-checkout')->click();

        $this->webDriver->wait(10, 500)->until(
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
        $this->webDriver->get(self::PS15URL);
        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('login')
            )
        );

        $this->findByClass('login')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('SubmitLogin')
            )
        );

        $this->findById('email')->sendKeys($this->configuration['email']);
        $this->findById('passwd')->sendKeys($this->configuration['password']);
        $this->findById('SubmitLogin')->click();
    }
}
