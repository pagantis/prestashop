<?php

namespace Test\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs17Selenium;

/**
 * @requires prestashop17basic
 * @group prestashop17install
 */
class PagantisPs17InstallTest extends AbstractPs17Selenium
{
    /**
     * @throws \Exception
     */
    public function testInstallAndConfigurePagantisInPrestashop17()
    {
        $this->loginToBackOffice();
        $this->uploadPagantis();
        $this->configurePagantis();
        $this->configureLanguagePack('72', 'Español (Spanish)');
        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function configurePagantis()
    {
        $this->findByCss('#pagantis_is_enabled_on + label')->click();
        $this->findById('pagantis_public_key')->clear()->sendKeys($this->configuration['publicKey']);
        $this->findById('pagantis_private_key')->clear()->sendKeys($this->configuration['secretKey']);
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
