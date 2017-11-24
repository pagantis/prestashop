<?php

namespace Test\Selenium\Install;

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
        $this->findByLinkText('Modules and Services')->click();
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
        $this->findByLinkText('Modules and Services')->click();
        $this->findById('moduleQuicksearch')->clear()->sendKeys('paga+tarde');

        try {
            $paylaterAnchor = $this->findById('anchorPaylater');
            $paylaterAnchorParent = $this->getParent($paylaterAnchor);
            $paylaterAnchorGrandParent = $this->getParent($paylaterAnchorParent);
            $this->moveToElementAndClick($paylaterAnchorGrandParent->findElement(
                WebDriverBy::partialLinkText('Install')
            ));
        } catch (\Exception $exception) {
            $paylaterAnchor = $this->findById('anchorPaylater');
            $paylaterAnchorParent = $this->getParent($paylaterAnchor);
            $paylaterAnchorGrandParent = $this->getParent($paylaterAnchorParent);
            $this->moveToElementAndClick($paylaterAnchorGrandParent->findElement(
                WebDriverBy::partialLinkText('Configure')
            ));
        }

        $verify = WebDriverBy::id('frame');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($verify);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findById('test')->click();
        $this->findById('PAYLATER_PUBLIC_KEY_TEST')->clear()->sendKeys($this->configuration['publicKey']);
        $this->findById('PAYLATER_PRIVATE_KEY_TEST')->clear()->sendKeys($this->configuration['secretKey']);
        $this->findById('PAYLATER_PUBLIC_KEY_PROD')->clear()->sendKeys('pk_this_is_fake');
        $this->findById('PAYLATER_PRIVATE_KEY_PROD')->clear()->sendKeys('this is a fake key');
        $this->findById('frame')->click();
        $selectorComboSearch = WebDriverBy::name('PAYLATER_PRODUCT_HOOK');
        /** @var RemoteWebElement[] $selectorElements */
        $selectorElements = $this->webDriver->findElements($selectorComboSearch);
        $selectorElements[3]->click();
        $selectorComboSearch = WebDriverBy::name('PAYLATER_PRODUCT_HOOK_TYPE');
        /** @var RemoteWebElement[] $selectorElements */
        $selectorElements = $this->webDriver->findElements($selectorComboSearch);
        $selectorElements[2]->click();
        $selectorComboSearch = WebDriverBy::name('PAYLATER_ADD_SIMULATOR');
        /** @var RemoteWebElement[] $selectorElements */
        $selectorElements = $this->webDriver->findElements($selectorComboSearch);
        $selectorElements[2]->click();
        $this->findById('PAYLATER_PROMOTION_EXTRA')->clear()->sendKeys($this->configuration['extra']);
        $this->findById('module_form_submit_btn');
        $confirmationSearch = WebDriverBy::className('module_confirmation');
        $condition = WebDriverExpectedCondition::textToBePresentInElement(
            $confirmationSearch,
            'All changes have been saved'
        );
        $this->webDriver->wait($condition);
        $this->assertTrue((bool) $condition);
    }
}
