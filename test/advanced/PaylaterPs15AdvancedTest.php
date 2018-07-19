<?php

namespace Test\Advanced;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs15Selenium;

/**
 * @requires prestashop15install
 * @requires prestashop15register
 *
 * @group prestashop15advanced
 */
class PaylaterPs15InstallTest extends AbstractPs15Selenium
{
    /**
     * @REQ5 BackOffice should have 2 inputs for setting the public and private API key
     * @REQ6 BackOffice inputs for API keys should be mandatory upon save of the form.
     *
     * @throws  \Exception
     */
    public function testPublicAndPrivateKeysInputs()
    {
        $this->loginToBackOffice();
        $this->getPaylaterBackOffice();

        //2 elements exist:
        $validatorSearch = WebDriverBy::id('pmt_public_key');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $validatorSearch = WebDriverBy::id('pmt_private_key');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);

        //save with empty public Key
        $this->findById('pmt_public_key')->clear();
        $this->findById('module_form_submit_btn')->click();
        $validatorSearch = WebDriverBy::className('module_error');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->assertContains('Please add a Paga+Tarde API Public Key', $this->webDriver->getPageSource());
        $this->findById('pmt_public_key')->clear()->sendKeys($this->configuration['publicKey']);

        //save with empty private Key
        $this->findById('pmt_private_key')->clear();
        $this->findById('module_form_submit_btn')->click();
        $validatorSearch = WebDriverBy::className('module_error');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->assertContains('Please add a Paga+Tarde API Private Key', $this->webDriver->getPageSource());
        $this->findById('pmt_private_key')->clear()->sendKeys($this->configuration['secretKey']);

        $this->quit();
    }

    /**
     * @REQ8 In the backOffice the merchant has to be able to choose between Iframe and Redirect.
     * (both cases should be tested in buy experience test).
     * Item title: "Visualización". Values: Redirect & Iframe. By default: Redirect
     *
     * @throws \Exception
     */
    public function testBuyWithRedirect()
    {
        $this->loginToBackOffice();
        $this->getPaylaterBackOffice();
        $this->findById('redirection')->click();
        $this->webDriver->executeScript('window.scrollBy(0,250)');
        $this->findById('module_form')->submit();
        $confirmationSearch = WebDriverBy::className('module_confirmation');
        $condition = WebDriverExpectedCondition::textToBePresentInElement(
            $confirmationSearch,
            'All changes have been saved'
        );
        $this->webDriver->wait($condition);
        $this->assertTrue((bool) $condition);

        $this->loginToFrontend();
        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout(true);
        $this->verifyUTF8();

        $this->webDriver->get(self::PS15URL.self::BACKOFFICE_FOLDER);
        $this->getPaylaterBackOffice();
        $this->findById('frame')->click();
        $this->findById('module_form_submit_btn')->click();
        $confirmationSearch = WebDriverBy::className('module_confirmation');
        $condition = WebDriverExpectedCondition::textToBePresentInElement(
            $confirmationSearch,
            'All changes have been saved'
        );
        $this->webDriver->wait($condition);
        $this->assertTrue((bool) $condition);
        $this->quit();
    }
}
