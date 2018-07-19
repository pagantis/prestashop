<?php

namespace Test\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs16Selenium;

/**
 * @requires prestashop16basic
 * @group prestashop16install
 */
class PaylaterPs16InstallTest extends AbstractPs16Selenium
{
    /**
     * @throws \Exception
     */
    public function testInstallAndConfigurePaylaterInPrestashop16()
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
        $this->findById('frame')->click();
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
