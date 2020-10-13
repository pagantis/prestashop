<?php

namespace Test\Advanced;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs17Selenium;

/**
 * @requires prestashop17install
 * @requires prestashop17register
 *
 * @group prestashop17advanced
 */
class ClearpayPs17InstallTest extends AbstractPs17Selenium
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
        $this->getClearpayBackOffice();

        //2 elements exist:
        $validatorSearch = WebDriverBy::id('CLEARPAY_PUBLIC_KEY');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $validatorSearch = WebDriverBy::id('clearpay_private_key');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);

        /* no longer checked in multiproduct
        //save with empty public Key
        $this->findById('CLEARPAY_PUBLIC_KEY')->clear();
        $this->findById('module_form_submit_btn')->click();
        $validatorSearch = WebDriverBy::className('module_error');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->assertContains('Please add a Clearpay API Public Key', $this->webDriver->getPageSource());
        $this->findById('CLEARPAY_PUBLIC_KEY')->clear()->sendKeys($this->configuration['publicKey']);

        //save with empty private Key
        $this->findById('clearpay_private_key')->clear();
        $this->findById('module_form_submit_btn')->click();
        $validatorSearch = WebDriverBy::className('module_error');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->assertContains('Please add a Clearpay API Private Key', $this->webDriver->getPageSource());
        $this->findById('clearpay_private_key')->clear()->sendKeys($this->configuration['secretKey']);
        */
        $this->quit();
    }

    /**
     * @REQ17 BackOffice Panel should have visible Logo and links
     *
     * @throws \Exception
     */
    public function testBackOfficeHasLogoAndLinkToClearpay()
    {
        //Change Title
        $this->loginToBackOffice();
        $this->getClearpayBackOffice();
        $html = $this->webDriver->getPageSource();
        $this->assertContains('pg.png', $html);
        $this->assertContains('Login Clearpay', $html);
        $this->assertContains('https://bo.clearpay.com', $html);
        $this->quit();
    }
}
