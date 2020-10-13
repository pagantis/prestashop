<?php

namespace Test\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs15Selenium;

/**
 * @requires prestashop15basic
 * @group prestashop15install
 */
class ClearpayPs15InstallTest extends AbstractPs15Selenium
{
    /**
     * @throws \Exception
     */
    public function testInstallAndConfigureClearpayInPrestashop15()
    {
        $this->loginToBackOffice();
        $this->uploadClearpay();
        $this->configureClearpay();
        $this->configureLanguagePack('Spain', 'Español (Spanish)');
        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function configureClearpay()
    {
        $this->findByLinkText('Modules')->click();
        $this->findByLinkText('Modules')->click();
        $this->findByName('quicksearch')->clear()->sendKeys('Clearpay');
        try {
            $this->findByLinkText('Install')->click();
        } catch (\Exception $exception) {
            $this->findByLinkText('Configure')->click();
        }

        $this->findByCss('#CLEARPAY_IS_ENABLED_true + label')->click();
        $this->findById('CLEARPAY_PUBLIC_KEY')->clear()->sendKeys('tk_8517351ec6ae44b29f5dca6e');
        $this->findById('clearpay_private_key')->clear()->sendKeys('c580df9e0b7b40c3');
        $this->findByCss('#CLEARPAY_ENVIRONMENT_sandbox + label')->click();
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
