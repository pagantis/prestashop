<?php

namespace Test\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs16Selenium;

/**
 * @requires prestashop16basic
 * @group prestashop16install
 */
class ClearpayPs16InstallTest extends AbstractPs16Selenium
{
    /**
     * @throws \Exception
     */
    public function testInstallAndConfigureClearpayInPrestashop16()
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
        $this->findByLinkText('Modules and Services')->click();
        $this->findById('moduleQuicksearch')->clear()->sendKeys('Clearpay');
        $this->installOrConfigureModule();

        // new prompt in module installation no thusted
        try {
            sleep(3);
            $this->findByCss('#moduleNotTrusted #proceed-install-anyway')->click();
        } catch (\Exception $exception) {
            // do nothing, no prompt
        };

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

    public function installOrConfigureModule()
    {
        try {
            $clearpayAnchor = $this->findById('anchorClearpay');
            $clearpayAnchorParent = $this->getParent($clearpayAnchor);
            $clearpayAnchorGrandParent = $this->getParent($clearpayAnchorParent);
            $this->moveToElementAndClick($clearpayAnchorGrandParent->findElement(
                WebDriverBy::partialLinkText('Install')
            ));
        } catch (\Exception $exception) {
            $clearpayAnchor = $this->findById('anchorClearpay');
            $clearpayAnchorParent = $this->getParent($clearpayAnchor);
            $clearpayAnchorGrandParent = $this->getParent($clearpayAnchorParent);
            $this->moveToElementAndClick($clearpayAnchorGrandParent->findElement(
                WebDriverBy::partialLinkText('Configure')
            ));
        }
    }
}
