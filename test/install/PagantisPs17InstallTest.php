<?php

namespace Test\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs17Selenium;

/**
 * @requires prestashop17basic
 * @group prestashop17install
 */
class ClearpayPs17InstallTest extends AbstractPs17Selenium
{
    /**
     * @throws \Exception
     */
    public function testInstallAndConfigureClearpayInPrestashop17()
    {
        $this->loginToBackOffice();
        $this->uploadClearpay();
        $this->configureClearpay();
        $this->configureLanguagePack('72', 'Español (Spanish)');
        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function configureClearpay()
    {
        $this->findByCss('#CLEARPAY_IS_ENABLED_on + label')->click();
        $this->findById('CLEARPAY_PUBLIC_KEY')->clear()->sendKeys('tk_8517351ec6ae44b29f5dca6e');
        $this->findById('clearpay_private_key')->clear()->sendKeys('c580df9e0b7b40c3');
        $this->findByCss('#CLEARPAY_ENVIRONMENT_sandbox + label')->click();
        $this->findById('module_form_submit_btn')->click();
        $confirmationSearch = WebDriverBy::className('module_confirmation');
        $condition = WebDriverExpectedCondition::textToBePresentInElement(
            $confirmationSearch,
            'All changes have been saved'
        );
        $this->webDriver->wait($condition);
        $this->assertTrue((bool) $condition);
    }
}
