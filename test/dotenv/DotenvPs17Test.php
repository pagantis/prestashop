<?php

namespace Test\Dotenv;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs17Selenium;

/**
 * @requires prestashop17install
 * @requires prestashop17register
 *
 * @group prestashop17dotenv
 */
class DotenvPs17Test extends AbstractPs17Selenium
{
    /**
     * @throws \Exception
     */
    public function testPmtTitleConfig()
    {
        // modify .env
        $properties = $this->getProperties();
        $properties['PMT_TITLE'] = 'Changed';
        $this->saveDotEnvFile($properties, '17');

        // run test
        $this->loginToFrontend();
        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout();

        $paylaterCheckout = WebDriverBy::cssSelector('#payment-option-3-container label span');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($paylaterCheckout);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $value = $this->webDriver->findElement($paylaterCheckout)->getText();

        $this->assertSame($properties['PMT_TITLE'], $value);

        // restore .env
        $this->saveDotEnvFile($this->getProperties(), '17');

        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function testPmtSimulatorDisplayTypeConfig()
    {
        // modify .env
        $properties = $this->getProperties();
        $properties['PMT_SIMULATOR_DISPLAY_TYPE'] = 'pmtSDK.simulator.types.TEXT';
        $this->saveDotEnvFile($properties, '17');

        // run test
        $this->loginToFrontend();
        $this->goToProduct();

        $value = $this->webDriver->executeScript('return pmtSDK.simulator.$pool.getAll()[0].simulatorConfig.type');
        $this->assertSame('text', $value);

        // restore .env
        $this->saveDotEnvFile($this->getProperties(), '17');

        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function testPmtSimulatorStartInstallmentsConfig()
    {
        // modify .env
        $properties = $this->getProperties();
        $properties['PMT_SIMULATOR_START_INSTALLMENTS'] = '6';
        $this->saveDotEnvFile($properties, '17');

        // run test
        $this->loginToFrontend();
        $this->goToProduct();

        $value = $this->webDriver->executeScript('return pmtSDK.simulator.$pool.getAll()[0].simulatorConfig.numInstalments');

        $this->assertSame($properties['PMT_SIMULATOR_START_INSTALLMENTS'], $value);

        // restore .env
        $this->saveDotEnvFile($this->getProperties(), '17');

        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function testPmtFormDisplayTypeConfig()
    {
        // modify .env
        $properties = $this->getProperties();
        $properties['PMT_FORM_DISPLAY_TYPE'] = '1';
        $this->saveDotEnvFile($properties, '17');

        // run test:wq
        $this->loginToFrontend();
        $this->goToProduct(false);
        $this->addProduct();
        $this->goToCheckout();

        $paylaterOption = WebDriverBy::cssSelector('[for=payment-option-3]');
        $condition = WebDriverExpectedCondition::elementToBeClickable($paylaterOption);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($paylaterOption)->click();

        $this->findById('conditions_to_approve[terms-and-conditions]')->click();
        $this->findById('payment-confirmation')->click();

        $firstIframe = $this->webDriver->findElement(WebDriverBy::tagName('iframe'));
        $condition = WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt($firstIframe);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);

        // restore .env
        $this->saveDotEnvFile($this->getProperties(), '17');

        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function testPmtDisplayMinAmountConfig()
    {
        // modify .env
        $properties = $this->getProperties();
        $properties['PMT_DISPLAY_MIN_AMOUNT'] = '5000';
        $this->saveDotEnvFile($properties, '17');

        // run test
        $this->loginToFrontend();
        $this->goToProduct(false);
        $this->addProduct();
        $this->goToCheckout();

        $paylaterCheckout = WebDriverBy::cssSelector('#payment-option-3-container label span');
        $this->assertFalse((bool) (count($this->webDriver->findElements($paylaterCheckout)) > 0));

        // restore .env
        $this->saveDotEnvFile($this->getProperties(), '17');

        $this->quit();
    }
}
