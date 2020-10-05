<?php

namespace Test\Validate;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Pagantis\SeleniumFormUtils\SeleniumHelper;
use Test\Common\AbstractPs17Selenium;

/**
 * @requires prestashop17install
 * @requires prestashop17register
 *
 * @group prestashop17validate
 */
class ValidatePs17Test extends AbstractPs17Selenium
{
    /**
     * @throws \Exception
     */
    public function testConfirmationPage()
    {
        $this->loginToBackOffice();
        $this->getClearpayBackOffice();
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
        $clearpayOption = WebDriverBy::cssSelector('[for=payment-option-3]');
        $condition = WebDriverExpectedCondition::elementToBeClickable($clearpayOption);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($clearpayOption)->click();

        var_dump("Pre title---->".$this->webDriver->getTitle());
        sleep(10);
        $this->findById('conditions_to_approve[terms-and-conditions]')->click();
        $this->findById('payment-confirmation')->click();

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
