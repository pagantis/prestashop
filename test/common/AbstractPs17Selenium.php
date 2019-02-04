<?php

namespace Test\Common;

use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use PagaMasTarde\SeleniumFormUtils\SeleniumHelper;
use Test\PaylaterPrestashopTest;

/**
 * Class AbstractPrestashop17CommonTest
 *
 * @package Test\Common
 */
abstract class AbstractPs17Selenium extends PaylaterPrestashopTest
{
    /**
     * @throws \Exception
     */
    public function loginToBackOffice()
    {
        $this->webDriver->get(self::PS17URL.self::BACKOFFICE_FOLDER);
        $emailElementSearch = WebDriverBy::id('email');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($emailElementSearch);
        $this->waitUntil($condition);
        $this->findById('email')->clear()->sendKeys($this->configuration['username']);
        $this->findById('passwd')->clear()->sendKeys($this->configuration['password']);
        $this->findById('login_form')->submit();
        $emailElementSearch = WebDriverBy::id('employee_infos');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($emailElementSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * @require loginToBackOffice
     *
     * @throws \Exception
     */
    public function uploadPaylater()
    {
        $this->webDriver->executeScript('document.querySelector(\'.onboarding-button-shut-down\').click();');
        sleep(10);
        $elementSearch = WebDriverBy::partialLinkText('Modules');
        $condition = WebDriverExpectedCondition::elementToBeClickable($elementSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findByLinkText('Modules')->click();
        $this->findById('page-header-desc-configuration-add_module')->click();
        $moduleInstallBlock = WebDriverBy::id('module_install');
        $fileInputSearch = $moduleInstallBlock->className('dz-hidden-input');
        $fileInput = $this->webDriver->findElement($fileInputSearch);
        $fileInput->setFileDetector(new LocalFileDetector());
        $fileInput->sendKeys(__DIR__.'/../../paylater.zip');
        $validatorSearch = WebDriverBy::className('module-import-success-msg');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->webDriver->wait(45, 1000)->until($condition);
        $this->assertTrue((bool) $condition);
        $this->findByClass('module-import-success-configure')->click();
    }

    /**
     * @require loginToBackOffice
     *
     * @throws \Exception
     */
    public function getPaylaterBackOffice()
    {
        $this->webDriver->get(self::PS17URL.self::BACKOFFICE_FOLDER);
        $this->findByLinkText('Modules')->click();
        $this->findByLinkText('Installed modules')->click();
        $this->findByClass('pstaggerAddTagInput')
            ->clear()
            ->sendKeys('Paga+Tarde')
            ->sendKeys(WebDriverKeys::ENTER)
        ;
        $this->findByClass('module_action_menu_configure')->click();
        $verify = WebDriverBy::id('pmt_public_key');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($verify);
        $this->waitUntil($condition);
    }

    /**
     * @throws \Exception
     */
    public function loginToFrontend()
    {
        $this->webDriver->get(self::PS17URL);
        $login = WebDriverBy::className('user-info');
        $condition = WebDriverExpectedCondition::elementToBeClickable($login);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($login)->click();
        $loginForm = WebDriverBy::id('login-form');
        $condition = WebDriverExpectedCondition::elementToBeClickable($loginForm);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findByName('email')->sendKeys($this->configuration['email']);
        $this->findByName('password')->sendKeys($this->configuration['password']);
        $this->webDriver->findElement($loginForm)->submit();
    }

    /**
     * @throws \Exception
     */
    public function createAccount()
    {
        $this->webDriver->get(self::PS17URL);
        $this->findByClass('user-info')->click();
        $this->findByClass('no-account')->click();
        $this->findByClass('custom-radio')->click();
        $this->findByName('firstname')->sendKeys($this->configuration['firstname']);
        $this->findByName('lastname')->sendKeys($this->configuration['lastname']);
        $this->findByName('email')->sendKeys($this->configuration['email']);
        $this->findByName('password')->sendKeys($this->configuration['password']);
        $this->findByName('birthday')->sendKeys($this->configuration['birthdate']);
        $this->findById('customer-form')->submit();
        try {
            $logoutButtonSearch = WebDriverBy::className('logout');
            $condition = WebDriverExpectedCondition::elementToBeClickable($logoutButtonSearch);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
            $this->findByClass('logout')->click();
        } catch (\Exception $exception) {
            $errorMessageSearch = WebDriverBy::className('help-block');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($errorMessageSearch);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
        }
    }

    /**
     * @param bool $addressExists
     *
     * @throws \Exception
     */
    public function goToCheckout($addressExists = false)
    {
        sleep(3);
        $cartButton = WebDriverBy::id('_desktop_cart');
        $condition = WebDriverExpectedCondition::elementToBeClickable($cartButton);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($cartButton)->click();
        $checkoutButton = WebDriverBy::className('cart-detailed-actions');
        $checkoutButton = $checkoutButton->className('btn-primary');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($checkoutButton);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->webDriver->findElement($checkoutButton)->click();
        try {
            if ($addressExists) {
                throw new \Exception('Address exists');
            }
            $addressInputSearch = WebDriverBy::name('firstname');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($addressInputSearch);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
            $this->findByName('company')->clear()->sendKeys($this->configuration['company']);
            $this->findByName('address1')->clear()->sendKeys('av.diagonal 579');
            $this->findByName('postcode')->clear()->sendKeys($this->configuration['zip']);
            $this->findByName('city')->clear()->sendKeys($this->configuration['city']);
            $this->findByName('phone')->clear()->sendKeys($this->configuration['phone']);
            $this->findById('delivery-address')->findElement(WebDriverBy::name('confirm-addresses'))->click();
            $processAddress = WebDriverBy::name('confirmDeliveryOption');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processAddress);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
        } catch (\Exception $exception) {
            $this->findByName('confirm-addresses')->click();
            $processAddress = WebDriverBy::name('confirmDeliveryOption');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processAddress);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
        }
        $this->webDriver->findElement($processAddress)->click();
        $processCarrier = WebDriverBy::id('payment-confirmation');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processCarrier);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($processCarrier)->click();
        $hookPayment = WebDriverBy::id('checkout-payment-step');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($hookPayment);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * @requires goToProduct
     *
     * @throws \Exception
     */
    public function addProduct()
    {
        $this->findByClass('add-to-cart')->click();
        $cartTitle = WebDriverBy::className('cart-products-count');
        /** @var WebDriverExpectedCondition $condition */
        $condition = WebDriverExpectedCondition::textToBePresentInElement($cartTitle, '(');
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        sleep(3);
        $this->webDriver->executeScript('document.querySelector(\'.close\').click();');
    }

    /**
     * @param bool $verifySimulator
     *
     * @throws \Exception
     */
    public function goToProduct($verifySimulator = true)
    {
        $this->webDriver->get(self::PS17URL);
        $this->findById('_desktop_logo')->click();
        $featuredProductCenterSearch = WebDriverBy::className('products');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($featuredProductCenterSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->findByClass('product-description')->click();
        $available = WebDriverBy::id('product-availability');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($available);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        if ($verifySimulator) {
            $pmtSimulator = WebDriverBy::className('PmtSimulator');
            $condition = WebDriverExpectedCondition::presenceOfElementLocated($pmtSimulator);
            $this->waitUntil($condition);
            $this->assertTrue((bool)$condition);
        }
    }

    /**
     * Verify paylater
     *
     * @throws \Exception
     */
    public function verifyPaylater()
    {
        $paylaterCheckout = WebDriverBy::cssSelector('[for=payment-option-3]');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($paylaterCheckout);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($paylaterCheckout)->click();

        $this->findById('conditions_to_approve[terms-and-conditions]')->click();
        $this->findById('payment-confirmation')->click();

        SeleniumHelper::finishForm($this->webDriver);
    }
}
