<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs17InstallTest
 * @package Test\Selenium\Basic
 *
 * @group prestashop17install
 * @group install
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
        //$this->quit();
    }

    /**
     * Login to the backoffice
     */
    public function loginToBackOffice()
    {
        $this->webDriver->get(self::PS17URL.self::BACKOFFICE_FOLDER);
        $emailElementSearch = WebDriverBy::id('email');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($emailElementSearch);
        $this->waitUntil($condition);
        $this->findById('email')->clear()->sendKeys($this->configuration['username']);
        $this->findById('passwd')->clear()->sendKeys($this->configuration['password']);
        $this->findById('login_form')->submit();
        $emailElementSearch = WebDriverBy::id('employee_infos');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($emailElementSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * Install PaylaterModule
     */
    public function uploadPaylaterModule()
    {
        $this->webDriver->executeScript('document.querySelector(\'.onboarding-button-shut-down\').click();');
        sleep(3);
        $elementSearch = WebDriverBy::partialLinkText('Modules');
        $condition = WebDriverExpectedCondition::elementToBeClickable($elementSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findByLinkText('Modules')->click();
        $this->findByLinkText('UPLOAD A MODULE')->click();
        $moduleInstallBlock = WebDriverBy::id('module_install');
        $fileInputSearch = $moduleInstallBlock->className('dz-hidden-input');
        $fileInput = $this->webDriver->findElement($fileInputSearch);
        $fileInput->setFileDetector(new LocalFileDetector());
        $fileInput->sendKeys(__DIR__.'/../../../paylater.zip');
        $validatorSearch = WebDriverBy::className('module-import-success-msg');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->webDriver->wait(45, 1000)->until($condition);
        $this->assertTrue((bool) $condition);

        $this->findByLinkText('CONFIGURE')->click();
    }

    /**
     * Configure paylater module
     */
    public function configureModule()
    {
        $verify = WebDriverBy::id('PAYLATER_PUBLIC_KEY_TEST');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($verify);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findById('test')->click();
        $this->findById('PAYLATER_PUBLIC_KEY_TEST')->clear()->sendKeys($this->configuration['publicKey']);
        $this->findById('PAYLATER_PRIVATE_KEY_TEST')->clear()->sendKeys($this->configuration['secretKey']);
        $this->findById('PAYLATER_PUBLIC_KEY_PROD')->clear()->sendKeys('pk_this_is_fake');
        $this->findById('PAYLATER_PRIVATE_KEY_PROD')->clear()->sendKeys('this is a fake key');
        $this->moveToElementAndClick($this->findByCss(
            'input[name="PAYLATER_IFRAME"][type="radio"][value="1"]'
        ));
        $this->moveToElementAndClick($this->findByCss(
            'input[name="PAYLATER_PRODUCT_HOOK"][type="radio"][value="hookDisplayRightColumnProduct"]'
        ));
        $this->moveToElementAndClick($this->findByCss(
            'input[name="PAYLATER_PRODUCT_HOOK_TYPE"][type="radio"][value="2"]'
        ));
        $this->moveToElementAndClick($this->findByCss(
            'input[name="PAYLATER_ADD_SIMULATOR"][type="radio"][value="2"]'
        ));
        $this->findById('PAYLATER_PROMOTION_EXTRA')->clear()->sendKeys($this->configuration['extra']);

        $this->moveToElementAndClick($this->findById('module_form_submit_btn'));
        $confirmationSearch = WebDriverBy::className('module_confirmation');
        $condition = WebDriverExpectedCondition::textToBePresentInElement(
            $confirmationSearch,
            'All changes have been saved'
        );
        $this->webDriver->wait($condition);
        $this->assertTrue((bool) $condition);
    }
}
