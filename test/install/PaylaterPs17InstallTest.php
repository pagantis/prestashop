<?php

namespace Test\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs17Selenium;

/**
 * @requires prestashop17basic
 * @group prestashop17install
 */
class PaylaterPs17InstallTest extends AbstractPs17Selenium
{
    /**
     * @throws \Exception
     */
    public function testInstallAndConfigurePaylaterInPrestashop17()
    {
        $this->loginToBackOffice();
        $this->uploadPaylater();
        $this->configurePaylater();
        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function configurePaylater()
    {
        $verify = WebDriverBy::id('redirection');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($verify);
        $this->waitUntil($condition);
        $this->findById('redirection')->click();
        $this->assertTrue((bool) $condition);
        $this->findById('pmt_public_key')->clear()->sendKeys($this->configuration['publicKey']);
        $this->findById('pmt_private_key')->clear()->sendKeys($this->configuration['secretKey']);
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
