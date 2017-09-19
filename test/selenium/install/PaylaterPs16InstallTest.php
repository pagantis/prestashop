<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs16InstallTest
 * @package Test\Selenium\Basic
 *
 * @group prestashop16install
 * @group install
 */
class PaylaterPs16InstallTest extends PaylaterPrestashopTest
{
    /**
     * testInstallPaylaterInPrestashop16
     */
    public function testInstallAndConfigurePaylaterInPrestashop16()
    {
        $this->loginToBackOffice();
        $this->uploadPaylaterModule();
        $this->configureModule();
        $this->quit();
    }

    /**
     * Login to the backoffice
     */
    public function loginToBackOffice()
    {
        $this->webDriver->get(self::PS16URL.self::BACKOFFICE_FOLDER);

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::name('submitLogin')
            )
        );

        $this->findByName('email')->sendKeys($this->configuration['username']);
        $this->findByName('passwd')->sendKeys($this->configuration['password']);
        $this->findByName('submitLogin')->click();

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::linkText('Modules and Services')
            )
        );

        $this->assertEquals('Dashboard • PrestaShop', $this->webDriver->getTitle());
    }

    /**
     * Install PaylaterModule
     */
    public function uploadPaylaterModule()
    {
        sleep(5);
        $this->findByLinkText('Modules and Services')->click();

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('desc-module-new')
            )
        );

        $this->findById('desc-module-new')->click();

        $fileInput = $this->findById('file');
        $fileInput->setFileDetector(new LocalFileDetector());

        try {
            $fileInput->sendKeys(__DIR__.'/../../../paylater.zip');
        } catch (StaleElementReferenceException $elementHasDisappeared) {
        }

        $this->webDriver->executeScript('
            document.querySelector(\'[name="download"]\').click();
        ');

        sleep(3);

        $this->assertContains(
            'The module was successfully downloaded',
            $this->findByClass('alert-success')->getText()
        );
    }

    /**
     * Configure paylater module
     */
    public function configureModule()
    {

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('module_type_filter')
            )
        );

        $this->findById('module_type_filter')->findElement(
            WebDriverBy::cssSelector("option[value='authorModules[paga+tarde]']")
        )->click();

        try {
            $this->findByClass('actions')->findElement(WebDriverBy::cssSelector('a'))->click();
        } catch (\Exception $exception) {
            $this->findByClass('actions')->findElement(WebDriverBy::cssSelector('a'))->click();
        }


        $this->assertContains(
            strtoupper('Paylater Configuration Panel'),
            $this->findByClass('paylater-content-form')->getText()
        );

        //Set it to test:
        $this->findById('test')->click();
        //Set Public and Private key:
        $this->findById('PAYLATER_PUBLIC_KEY_TEST')->clear()->sendKeys($this->configuration['publicKey']);
        $this->findById('PAYLATER_PRIVATE_KEY_TEST')->clear()->sendKeys($this->configuration['secretKey']);
        $this->findById('PAYLATER_PUBLIC_KEY_PROD')->clear()->sendKeys('pk_this_is_fake');
        $this->findById('PAYLATER_PRIVATE_KEY_PROD')->clear()->sendKeys('this is a fake key');
        $this->webDriver->executeScript('
            document.querySelector(\'input[name="PAYLATER_ADD_SIMULATOR"][type="radio"][value="1"]\').click();
            document.querySelector(\'input[name="PAYLATER_IFRAME"][type="radio"][value="1"]\').click();
            document.getElementById(\'module_form_submit_btn\').scrollIntoView();
        ');

        $this->findById('module_form_submit_btn')->click();

        $this->webDriver->wait(3, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('module_confirmation')
            )
        );

        $this->assertContains(
            'All changes have been saved',
            $this->findByClass('module_confirmation')->getText()
        );
    }
}
