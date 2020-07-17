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

        $this->findByCss('#p12x_is_enabled_true + label')->click();
        $this->findById('p12x_public_key')->clear()->sendKeys('tk_8517351ec6ae44b29f5dca6e');
        $this->findById('p12x_private_key')->clear()->sendKeys('13e3ca35bdae432d');
        $this->findByCss('#p12x_simulator_is_enabled_on + label')->click();
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
