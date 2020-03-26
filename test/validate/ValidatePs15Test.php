<?php

namespace Test\Validate;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Pagantis\SeleniumFormUtils\SeleniumHelper;
use Test\Common\AbstractPs15Selenium;

/**
 * @requires prestashop15install
 * @requires prestashop15register
 *
 * @group prestashop15validate
 */
class ValidatePs15Test extends AbstractPs15Selenium
{
    /**
     * @throws \Exception
     */
    public function testConfirmationPage()
    {
        $this->loginToBackOffice();
        $this->getPagantisBackOffice();
        $this->findById('module_form_submit_btn')->click();
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
        $pagantisCheckout = WebDriverBy::className('pagantis-checkout');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($pagantisCheckout);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);

        var_dump("Validate Pre title---->".$this->webDriver->getTitle());
        sleep(10);
        //$this->webDriver->findElement($pagantisCheckout)->click();
        $this->webDriver->executeScript('document.querySelector(\'.pagantis-checkout\').click();');

        var_dump("validate out title---->".$this->webDriver->getTitle());
        SeleniumHelper::finishForm($this->webDriver);
        sleep(10);
        var_dump("validate Pos title---->".$this->webDriver->getTitle());

        $confirmationMessage = WebDriverBy::id('order-confirmation');
        $condition = WebDriverExpectedCondition::presenceOfElementLocated(
            $confirmationMessage
        );
        $this->webDriver->wait($condition);
        $this->assertTrue((bool) $condition);
        $this->quit();
    }
}
