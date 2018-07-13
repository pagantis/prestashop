<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriver;
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
        $this->findByLinkText('New module')->click();
        $this->findByLinkText('Add a new module')->click();
        $moduleInstallBlock = WebDriverBy::id('module_install');
        $fileInputSearch = $moduleInstallBlock->name('file');
        $fileInput = $this->webDriver->findElement($fileInputSearch);
        $fileInput->setFileDetector(new LocalFileDetector());
        $fileInput->sendKeys(__DIR__.'/../../../paylater.zip');
        $fileInput->submit();
        $validatorSearch = WebDriverBy::id('anchorPaylater');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * Configure paylater module
     */
    public function configureModule()
    {
        $this->findByLinkText('Modules')->click();
        $this->findByLinkText('Modules')->click();
        $this->findByName('quicksearch')->clear()->sendKeys('paga+tarde');

        try {
            $this->findByLinkText('Install')->click();
        } catch (\Exception $exception) {
            $this->findByLinkText('Configure')->click();
        }

        $verify = WebDriverBy::id('frame');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($verify);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findById('frame')->click();
        $this->findById('pmt_public_key')->clear()->sendKeys($this->configuration['publicKey']);
        $this->findById('pmt_private_key')->clear()->sendKeys($this->configuration['secretKey']);
        $this->webDriver->executeScript('window.scrollBy(0,250)');
        $this->findById('module_form')->submit();
        $confirmationSearch = WebDriverBy::className('module_confirmation');
        $condition = WebDriverExpectedCondition::textToBePresentInElement(
            $confirmationSearch,
            'All changes have been saved'
        );
        $this->webDriver->wait($condition);
        $this->assertTrue((bool) $condition);
    }
}
