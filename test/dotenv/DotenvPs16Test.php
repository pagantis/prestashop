<?php

namespace Test\Dotenv;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs16Selenium;

/**
 * @requires prestashop16install
 * @requires prestashop16register
 *
 * @group prestashop16dotenv
 */
class DotenvPs16Test extends AbstractPs16Selenium
{
    /**
     * @throws \Exception
     */
    public function testPmtTitleConfig()
    {
        // modify .env
        $properties = $this->getProperties();
        $properties['PMT_TITLE'] = 'Changed';
        $this->saveDotEnvFile($properties, '16');

        // run test
        $this->loginToFrontend();
        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout();

        $paylaterCheckout = WebDriverBy::className('paylater-checkout');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($paylaterCheckout);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $value = $this->webDriver->findElement($paylaterCheckout)->getAttribute('title');

        $this->assertSame($properties['PMT_TITLE'], $value);

        // restore .env
        $this->saveDotEnvFile($this->getProperties(), '16');

        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function testPmtSimulatorDisplayTypeConfig()
    {
        // modify .env
        $properties = $this->getProperties();
        $properties['PMT_SIMULATOR_DISPLAY_TYPE'] = '3';
        $this->saveDotEnvFile($properties, '16');

        // run test
        $this->loginToFrontend();
        $this->goToProduct();

        $simulator = WebDriverBy::className('PmtSimulator');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($simulator);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $value = $this->webDriver->findElement($simulator)->getAttribute('data-pmt-type');

        $this->assertSame($properties['PMT_SIMULATOR_DISPLAY_TYPE'], $value);

        // restore .env
        $this->saveDotEnvFile($this->getProperties(), '16');

        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function testPmtSimulatorDisplayPositionConfig()
    {
        // modify .env
        $properties = $this->getProperties();
        $properties['PMT_SIMULATOR_DISPLAY_POSITION'] = '\'hookDisplayLeftColumnProduct\'';
        $this->saveDotEnvFile($properties, '16');

        // run test
        $this->loginToFrontend();
        $this->goToProduct();

        $simulator = WebDriverBy::cssSelector('.pb-center-column .PmtSimulator');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($simulator);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);

        // restore .env
        $this->saveDotEnvFile($this->getProperties(), '16');

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
        $this->saveDotEnvFile($properties, '16');

        // run test
        $this->loginToFrontend();
        $this->goToProduct();

        $simulator = WebDriverBy::className('PmtSimulator');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($simulator);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $value = $this->webDriver->findElement($simulator)->getAttribute('data-pmt-num-quota');

        $this->assertSame($properties['PMT_SIMULATOR_START_INSTALLMENTS'], $value);

        // restore .env
        $this->saveDotEnvFile($this->getProperties(), '16');

        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function testPmtSimulatorMaxInstallmentsConfig()
    {
        // modify .env
        $properties = $this->getProperties();
        $properties['PMT_SIMULATOR_MAX_INSTALLMENTS'] = '10';
        $this->saveDotEnvFile($properties, '16');

        // run test
        $this->loginToFrontend();
        $this->goToProduct();

        $simulator = WebDriverBy::className('PmtSimulator');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($simulator);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $value = $this->webDriver->findElement($simulator)->getAttribute('data-pmt-max-ins');

        $this->assertSame($properties['PMT_SIMULATOR_MAX_INSTALLMENTS'], $value);

        // restore .env
        $this->saveDotEnvFile($this->getProperties(), '16');

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
        $this->saveDotEnvFile($properties, '16');

        // run test
        $this->loginToFrontend();
        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout();

        $paylaterCheckout = WebDriverBy::className('paylater-checkout');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($paylaterCheckout);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($paylaterCheckout)->click();

        $firstIframe = $this->webDriver->findElement(WebDriverBy::tagName('iframe'));
        $condition = WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt($firstIframe);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);

        // restore .env
        $this->saveDotEnvFile($this->getProperties(), '16');

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
        $this->saveDotEnvFile($properties, '16');

        // run test
        $this->loginToFrontend();
        $this->goToProduct(false);
        $this->addProduct();
        $this->goToCheckout();

        $paylaterCheckout = WebDriverBy::className('paylater-checkout');
        $this->assertFalse((bool) (count($this->webDriver->findElements($paylaterCheckout)) > 0));

        // restore .env
        $this->saveDotEnvFile($this->getProperties(), '16');

        $this->quit();
    }
}
