<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs17InstallTest
 * @package Test\Selenium\Basic
 *
 * @group prestashop17install
 */
class PaylaterPs17InstallTest extends PaylaterPrestashopTest
{
    /**
     * testInstallPaylaterInPrestashop17
     */
    public function testInstallAndConfigurePaylaterInPrestashop17()
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
        $this->webDriver->get(self::PS17URL.self::BACKOFFICE_FOLDER);

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
                WebDriverBy::linkText('Modules')
            )
        );

        $this->assertEquals('Dashboard • PrestaShop', $this->webDriver->getTitle());
    }

    /**
     * Install PaylaterModule
     */
    public function uploadPaylaterModule()
    {
        $this->webDriver->executeScript('document.querySelector(\'.onboarding-button-shut-down\').click();');
        sleep(3);
        $this->findByLinkText('Modules')->click();

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::linkText('Installed modules')
            )
        );

        $this->findByLinkText('Installed modules')->click();

        $this->webDriver->wait(10, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('page-header-desc-configuration-add_module')
            )
        );

        $this->findById('page-header-desc-configuration-add_module')->click();

        $fileInput = $this->findByClass('dz-hidden-input');
        $fileInput->setFileDetector(new LocalFileDetector());

        try {
            $fileInput->sendKeys(__DIR__.'/../../../paylater.zip')->submit();
        } catch (StaleElementReferenceException $elementHasDisappeared) {
        }

        $this->webDriver->wait()->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('module-import-success-configure')
            )
        );

        $this->assertContains(
            'Module installed!',
            $this->findByClass('module-import-success-msg')->getText()
        );
    }

    /**
     * Configure paylater module
     */
    public function configureModule()
    {
        $this->findByClass('module-import-success-configure')->click();

        sleep(3);

        $this->assertContains(
            'Paylater Configuration Panel',
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
