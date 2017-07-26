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
        $this->addProductAndGoToCheckout();
        $this->quit();
    }

    public function addProductAndGoToCheckout()
    {
        $this->findById('category-3')->click();
        $this->findByClass('thumbnail-container')->click();
        $this->findByClass('bootstrap-touchspin-up')->click();
        $this->findByClass('add-to-cart')->click();
        sleep(1);
        $this->findByClass('close')->click();
        sleep(1);
        $this->findByClass('shopping-cart')->click();
        $this->findByClass('btn-primary')->click();
        try {
            $editAddress = $this->findByClass('edit-address ');
            $editAddress->click();
        } catch (\Exception $exception) {
            //already input address
        }

        $this->findByName('address1')->clear()->sendKeys('My house');
        $this->findByName('postcode')->clear()->sendKeys('00800');
        $this->findByName('city')->clear()->sendKeys('My city');
        $this->findByClass('btn-primary')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::name('confirmDeliveryOption')
            )
        );

        $this->findByName('confirmDeliveryOption')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('payment-option-3')
            )
        );

        $this->findById('payment-option-3')->click();

        //check we have the simulator:
        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('conditions_to_approve[terms-and-conditions]')
            )
        );

        $this->findById('conditions_to_approve[terms-and-conditions]')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('payment-confirmation'))
        );
        $this->findById('payment-confirmation')->click();

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
        $this->webDriver->get(self::PS17URL);
        $this->findByClass('user-info')->click();
        $this->findByName('email')->sendKeys($this->configuration['email']);
        $this->findByName('password')->sendKeys($this->configuration['password']);
        $this->webDriver->executeScript('document.getElementById(\'login-form\').submit();');
    }
}
