<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs17BuyTest
 * @package Test\Selenium\Basic
 *
 * @group prestashop17
 * @group buy
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
        try {
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
            $this->findByName('confirmDeliveryOption')->click();
            $this->findById('payment-option-3')->click();

            //check we have the simulator:

            $this->findById('conditions_to_approve[terms-and-conditions]')->click();

            $this->assertContains(
                'Paga+Tarde',
                $this->webDriver->findElement(WebDriverBy::tagName('body'))->getText()
            );

            sleep(10);
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * LOGIN
     */
    public function login()
    {
        try {
            $this->webDriver->get(self::PS17URL);
            $this->findByClass('user-info')->click();
            $this->findByName('email')->sendKeys($this->configuration['email']);
            $this->findByName('password')->sendKeys($this->configuration['password']);
            $this->webDriver->executeScript('document.getElementById(\'login-form\').submit();');
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }
}
