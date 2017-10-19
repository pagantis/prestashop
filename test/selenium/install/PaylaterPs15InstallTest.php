<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs15InstallTest
 * @package Test\Selenium\Basic
 *
 * @group prestashop15install
 * @group install
 */
class PaylaterPs15InstallTest extends PaylaterPrestashopTest
{
    /**
     * testInstallPaylaterInPrestashop15
     */
    public function testInstallAndConfigurePaylaterInPrestashop15()
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
        $this->webDriver->get(self::PS15URL.self::BACKOFFICE_FOLDER);

        $this->webDriver->wait(3, 50)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::name('submitLogin')
            )
        );

        $this->findByName('email')->sendKeys($this->configuration['username']);
        $this->findByName('passwd')->sendKeys($this->configuration['password']);
        $this->findByName('submitLogin')->click();

        $this->webDriver->wait(3, 50)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('maintab15')
            )
        );

        $this->assertContains('Home - PrestaShop', $this->webDriver->getTitle());
    }

    /**
     * Install PaylaterModule
     */
    public function uploadPaylaterModule()
    {
        $this->findById('maintab15')->click();

        $this->webDriver->wait(3, 100)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::linkText('Modules')
            )
        );

        $this->findById('fifth_block')->findElement(WebDriverBy::cssSelector('a'))->click();

        $this->webDriver->wait(3, 100)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('desc-module-new')
            )
        );

        $this->webDriver->executeScript('document.getElementById("desc-module-new").click();');

        $this->webDriver->wait(3, 100)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::name('download')
            )
        );

        $fileInput = $this->findByName('file');
        $fileInput->setFileDetector(new LocalFileDetector());

        try {
            $fileInput->sendKeys(__DIR__.'/../../../paylater.zip');
        } catch (StaleElementReferenceException $elementHasDisappeared) {
        }

        $this->findByName('download')->click();

        $this->webDriver->wait(5, 50)->until(
            WebDriverExpectedCondition::textToBePresentInElement(
                WebDriverBy::id('anchorPaylater'),
                'Paga+Tarde'
            )
        );

        $this->assertContains(
            'The module was downloaded successfully',
            $this->findByClass('conf')->getText()
        );
    }

    /**
     * Configure paylater module
     */
    public function configureModule()
    {
        $this->findById('module_type_filter')->findElement(
            WebDriverBy::cssSelector("option[value='authorModules[paga+tarde]']")
        )->click();

        sleep(1);

        if ($this->findById('anchorPaylater')->findElement(
            WebDriverBy::className('setup')
        )->getText() == 'NOT INSTALLED') {
            $this->findById('list-action-button')->findElement(WebDriverBy::cssSelector('li'))->click();
            $this->webDriver->wait(3, 50)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::name('submitpaylater')
                )
            );
        } else {
            $this->findById('anchorPaylater')->findElement(WebDriverBy::linkText('Configure'))->click();
        }

        $this->webDriver->wait(5, 50)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::name('submitpaylater')
            )
        );

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
