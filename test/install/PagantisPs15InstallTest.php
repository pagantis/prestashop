<?php

namespace Test\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs15Selenium;

/**
 * @requires prestashop15basic
 * @group prestashop15install
 */
class PagantisPs15InstallTest extends AbstractPs15Selenium
{
    /**
     * @throws \Exception
     */
    public function testInstallAndConfigurePagantisInPrestashop15()
    {
        $this->loginToBackOffice();
        $this->uploadPagantis();
        $this->configurePagantis();
        $this->configureLanguagePack('Spain', 'Español (Spanish)');
        $this->quit();
    }

    /**
     * @throws \Exception
     */
    public function configurePagantis()
    {
        $this->findByLinkText('Modules')->click();
        $this->findByLinkText('Modules')->click();
        $this->findByName('quicksearch')->clear()->sendKeys('Pagantis');
        try {
            $this->findByLinkText('Install')->click();
        } catch (\Exception $exception) {
            $this->findByLinkText('Configure')->click();
        }

        $this->findByCss('#pagantis_is_enabled_true + label')->click();
        $this->findById('pagantis_public_key')->clear()->sendKeys('tk_2ec8af2e894739f3eccc0921');
        $this->findById('pagantis_private_key')->clear()->sendKeys('3af9065648bf1970');
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
