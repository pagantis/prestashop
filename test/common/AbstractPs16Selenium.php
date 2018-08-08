<?php

namespace Test\Common;

use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\PaylaterPrestashopTest;

/**
 * Class AbstractPrestashop16CommonTest
 *
 * @package Test\Common
 */
abstract class AbstractPs16Selenium extends PaylaterPrestashopTest
{
    /**
     * @throws \Exception
     */
    public function loginToBackOffice()
    {
        $this->webDriver->get(self::PS16URL.self::BACKOFFICE_FOLDER);
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
        $this->findById('maintab-AdminParentModules')->click();
        $this->findByLinkText('Add a new module')->click();
        $moduleInstallBlock = WebDriverBy::id('module_install');
        $fileInputSearch = $moduleInstallBlock->name('file');
        $fileInput = $this->webDriver->findElement($fileInputSearch);
        $fileInput->setFileDetector(new LocalFileDetector());
        $fileInput->sendKeys(__DIR__.'/../../paylater.zip');
        $this->findByName('download')->click();
        $validatorSearch = WebDriverBy::id('anchorPaylater');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * @require loginToBackOffice
     *
     * @throws \Exception
     */
    public function getPaylaterBackOffice()
    {
        $this->webDriver->get(self::PS16URL.self::BACKOFFICE_FOLDER);
        $this->findByLinkText('Modules and Services')->click();
        $this->findById('moduleQuicksearch')->clear()->sendKeys('paga+tarde');
        $paylaterAnchor = $this->findById('anchorPaylater');
        $paylaterAnchorParent = $this->getParent($paylaterAnchor);
        $paylaterAnchorGrandParent = $this->getParent($paylaterAnchorParent);
        $this->moveToElementAndClick($paylaterAnchorGrandParent->findElement(
            WebDriverBy::partialLinkText('Configure')
        ));
        $verify = WebDriverBy::id('frame');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($verify);
        $this->waitUntil($condition);
    }

    /**
     * @throws \Exception
     */
    public function loginToFrontend()
    {
        $this->webDriver->get(self::PS16URL);
        $loginButtonSearch = WebDriverBy::className('login');
        $condition = WebDriverExpectedCondition::elementToBeClickable($loginButtonSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($loginButtonSearch)->click();
        $submitLoginButtonSearch = WebDriverBy::id('SubmitLogin');
        $condition = WebDriverExpectedCondition::elementToBeClickable($submitLoginButtonSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findById('email')->sendKeys($this->configuration['email']);
        $this->findById('passwd')->sendKeys($this->configuration['password']);
        $this->findById('SubmitLogin')->click();
        $logoutButtonSearch = WebDriverBy::className('logout');
        $condition = WebDriverExpectedCondition::elementToBeClickable($logoutButtonSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * @throws \Exception
     */
    public function createAccount()
    {
        $this->webDriver->get(self::PS16URL);
        $loginButtonSearch = WebDriverBy::className('login');
        $condition = WebDriverExpectedCondition::elementToBeClickable($loginButtonSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($loginButtonSearch)->click();
        $this->findById('email_create')->sendKeys($this->configuration['email']);
        $this->findById('SubmitCreate')->click();
        $submitAccountSearch = WebDriverBy::id('customer_firstname');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($submitAccountSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findById('id_gender1')->click();
        $this->findById('customer_firstname')->clear()->sendKeys($this->configuration['firstname']);
        $this->findById('customer_lastname')->sendKeys($this->configuration['lastname']);
        $this->findById('passwd')->sendKeys($this->configuration['password']);
        $this->findById('days')->sendKeys(1);
        $this->findById('months')->sendKeys('January');
        $this->findById('years')->sendKeys(1990);
        $this->findById('submitAccount')->click();
        $logoutButtonSearch = WebDriverBy::className('logout');
        $condition = WebDriverExpectedCondition::elementToBeClickable($logoutButtonSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($logoutButtonSearch)->click();
    }

    /**
     * @param bool $addressExists
     * @param bool $verifySimulator
     *
     * @throws \Exception
     */
    public function goToCheckout($addressExists = false, $verifySimulator = true)
    {
        $this->webDriver->get(self::PS16URL);
        $shoppingCart = WebDriverBy::className('shopping_cart');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated(
            $shoppingCart
        );
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->findByLinkText('Cart')->click();
        $checkoutButton = WebDriverBy::className('standard-checkout');
        $condition      = WebDriverExpectedCondition::visibilityOfElementLocated($checkoutButton);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->webDriver->findElement($checkoutButton)->click();
        try {
            if ($addressExists) {
                throw new \Exception('Address exists');
            }
            $addressInputSearch = WebDriverBy::id('firstname');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($addressInputSearch);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
            $this->findById('company')->clear()->sendKeys($this->configuration['company']);
            $this->findById('address1')->clear()->sendKeys('av.diagonal 579');
            $this->findById('postcode')->clear()->sendKeys($this->configuration['zip']);
            $this->findById('city')->clear()->sendKeys($this->configuration['city']);
            $this->findById('phone')->clear()->sendKeys($this->configuration['phone']);
            $this->findById('phone_mobile')->clear()->sendKeys($this->configuration['phone']);
            $this->findById('dni')->clear()->sendKeys($this->configuration['dni']);
            $this->moveToElementAndClick($this->findById('submitAddress'));
            $processAddress = WebDriverBy::name('processAddress');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processAddress);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
        } catch (\Exception $exception) {
            $processAddress = WebDriverBy::name('processAddress');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processAddress);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
        }
        $this->webDriver->findElement($processAddress)->click();
        $processCarrier = WebDriverBy::name('processCarrier');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processCarrier);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findById('cgv')->click();
        $this->webDriver->findElement($processCarrier)->click();
        $hookPayment = WebDriverBy::id('HOOK_PAYMENT');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($hookPayment);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        if ($verifySimulator) {
            //TODO UNCOMMENT THIS WHEN ORDERS HAVE SIMULATOR
            /*
            $pmtSimulator = WebDriverBy::className('PmtSimulator');
            $condition = WebDriverExpectedCondition::presenceOfElementLocated($pmtSimulator);
            $this->waitUntil($condition);
            $this->assertTrue((bool)$condition);
            */
        }
    }

    /**
     * @requires goToProduct
     *
     * @throws \Exception
     */
    public function addProduct()
    {
        $this->findById('buy_block')->submit();
        $cartTitle = WebDriverBy::id('cart_title');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($cartTitle);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * @param bool $verifySimulator
     *
     * @throws \Exception
     */
    public function goToProduct($verifySimulator = true)
    {
        $this->webDriver->get(self::PS16URL);
        $this->findById('header_logo')->click();
        $featuredProductCenterSearch = WebDriverBy::id('center_column');
        $condition                   = WebDriverExpectedCondition::visibilityOfElementLocated(
            $featuredProductCenterSearch
        );
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->moveToElementAndClick($this->findByClass('new-box'));
        $available = WebDriverBy::id('availability_statut');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($available);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        if ($verifySimulator) {
            //TODO UNCOMMENT THIS WHEN ORDERS HAVE SIMULATOR
            /*
            $pmtSimulator = WebDriverBy::className('PmtSimulator');
            $condition = WebDriverExpectedCondition::presenceOfElementLocated($pmtSimulator);
            $this->waitUntil($condition);
            $this->assertTrue((bool)$condition);
            */
        }
    }

    /**
     * @throws \Exception
     */
    public function verifyUTF8()
    {
        $paymentFormElement = WebDriverBy::className('FieldsPreview-desc');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($paymentFormElement);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->assertSame(
            $this->configuration['firstname'] . ' ' . $this->configuration['lastname'],
            $this->findByClass('FieldsPreview-desc')->getText()
        );
    }

    /**
     * Verify Paylater iframe
     *
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function verifyPaylater()
    {
        $paylaterCheckout = WebDriverBy::className('paylater-checkout');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($paylaterCheckout);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($paylaterCheckout)->click();
        $modulePayment = $this->webDriver->findElement(WebDriverBy::id('module-paylater-payment'));
        $firstIframe = $modulePayment->findElement(WebDriverBy::tagName('iframe'));
        $condition = WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt($firstIframe);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $pmtModal = WebDriverBy::id('pmtmodal');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($pmtModal);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $iFrame = 'pmtmodal_iframe';
        $condition = WebDriverExpectedCondition::frameToBeAvailableAndSwitchToIt($iFrame);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $paymentFormElement = WebDriverBy::className('Notification-text');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($paymentFormElement);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->assertContains(
            'compra',
            $this->findByClass('Form-heading1')->getText()
        );
    }
}
