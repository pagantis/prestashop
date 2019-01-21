<?php

namespace Test\Dotenv;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PagaMasTarde\SeleniumFormUtils\SeleniumHelper;
use Test\Common\AbstractPs15Selenium;

/**
 * @requires prestashop15install
 * @requires prestashop15register
 *
 * @group prestashop15dotenv
 */
class DotenvPs15Test extends AbstractPs15Selenium
{
    /**
     * @throws \Exception
     */
    public function testPmtTitleConfig()
    {
        // modify .env
        $properties = $this->getProperties();
        $properties['PMT_TITLE'] = '\'Changed\'';
        $this->saveDotEnvFile($properties);

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

        var_dump($properties['PMT_TITLE'], $value);
        $this->assertSame($properties['PMT_TITLE'], $value);

        // restore .env
        $this->saveDotEnvFile($this->getProperties());

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
        $this->saveDotEnvFile($properties);

        // run test
        $this->loginToFrontend();
        $this->goToProduct();

        $simulator = WebDriverBy::className('PmtSimulator');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($simulator);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $value = $this->webDriver->findElement($paylaterCheckout)->getAttribute('data-pmt-type');

        $this->assertSame($properties['PMT_SIMULATOR_DISPLAY_TYPE'], $value);

        // restore .env
        $this->saveDotEnvFile($this->getProperties());

        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function testPmtSimulatorDisplayPositionConfig()
    {
        // modify .env
        $properties = $this->getProperties();
        $properties['PMT_SIMULATOR_DISPLAY_POSITION'] = '\'hookDisplayRightColumn\'';
        $this->saveDotEnvFile($properties);

        // run test
        $this->loginToFrontend();
        $this->goToProduct();

        $simulator = WebDriverBy::cssSelector('#right_column .PmtSimulator');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($simulator);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);

        // restore .env
        $this->saveDotEnvFile($this->getProperties());

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
        $this->saveDotEnvFile($properties);

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
        $this->saveDotEnvFile($this->getProperties());

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
        $this->saveDotEnvFile($properties);

        // run test
        $this->loginToFrontend();
        $this->goToProduct();

        $simulator = WebDriverBy::className('PmtSimulator');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($simulator);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $value = $this->webDriver->findElement($paylaterCheckout)->getAttribute('data-pmt-max-ins');

        $this->assertSame($properties['PMT_SIMULATOR_MAX_INSTALLMENTS'], $value);

        // restore .env
        $this->saveDotEnvFile($this->getProperties());

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
        $this->saveDotEnvFile($properties);

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

        // $this->assertSame($properties['PMT_FORM_DISPLAY_TYPE'], $value);

        // in progress
        // restore .env
        $this->saveDotEnvFile($this->getProperties());

        $this->quit();
    }
}
