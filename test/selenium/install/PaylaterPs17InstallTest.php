<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTestCase;

/**
 * Class PaylaterPs17InstallTest
 * @package Test\Selenium\Basic
 *
 * @group ps17
 */
class PaylaterPs17InstallTest extends PaylaterPrestashopTestCase
{
    /**
     * testInstallPaylaterInPrestashop17
     */
    public function testInstallAndConfigurePaylaterInPrestashop17()
    {
        $this->loginToBackOffice();

        $this->webDriver->findElement(WebDriverBy::linkText('Modules'))->click();
        $this->webDriver->findElement(WebDriverBy::linkText('Installed modules'))->click();
        $this->webDriver->findElement(WebDriverBy::id('page-header-desc-configuration-add_module'))->click();

        // getting the input element
        $fileInput = $this->webDriver->findElement(WebDriverBy::className('dz-hidden-input'));
        $fileInput->setFileDetector(new LocalFileDetector());

        try {
            $fileInput->sendKeys(__DIR__.'/../../../paylater.zip')->submit();
        } catch (StaleElementReferenceException $elementHasDisappeared) {
        }

        $this->webDriver->wait(20, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('module-import-success-configure')
            )
        );

        $this->webDriver->findElement(WebDriverBy::className('module-import-success-configure'))->click();

        $this->webDriver->wait(20, 500)->until(
            WebDriverExpectedCondition::textToBePresentInElement(
                WebDriverBy::className('module-import-success-configure'),
                'Paylater Configuration Panel'
            )
        );

        $this->assertTrue(WebDriverExpectedCondition::textToBePresentInElement(
            WebDriverBy::className('module-import-success-configure'),
            'Paylater Configuration Panel'
        ));
    }

    /**
     * Login to the backoffice
     */
    public function loginToBackOffice()
    {
        $this->webDriver->findElement(WebDriverBy::name('email'))->sendKeys($this->configuration['username']);
        $this->webDriver->findElement(WebDriverBy::name('passwd'))->sendKeys($this->configuration['password']);
        $this->webDriver->findElement(WebDriverBy::name('submitLogin'))->click();

        $this->webDriver->wait(10, 5000)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::linkText('Modules')
            )
        );

        $this->assertEquals('Dashboard Prestashop', $this->webDriver->getTitle());
    }
}
