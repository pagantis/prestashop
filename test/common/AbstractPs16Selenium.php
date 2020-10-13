<?php

namespace Test\Common;

use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;
use Clearpay\SeleniumFormUtils\SeleniumHelper;
use Test\ClearpayPrestashopTest;

/**
 * Class AbstractPrestashop16CommonTest
 *
 * @package Test\Common
 */
abstract class AbstractPs16Selenium extends ClearpayPrestashopTest
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
    public function uploadClearpay()
    {
        $this->findById('maintab-AdminParentModules')->click();
        $this->findByLinkText('Add a new module')->click();
        $moduleInstallBlock = WebDriverBy::id('module_install');
        $fileInputSearch = $moduleInstallBlock->name('file');
        $fileInput = $this->webDriver->findElement($fileInputSearch);
        $fileInput->setFileDetector(new LocalFileDetector());
        $fileInput->sendKeys(__DIR__.'/../../clearpay.zip');
        $submitButton = WebDriverBy::name('download');
        $condition = WebDriverExpectedCondition::elementToBeClickable($submitButton);
        $this->waitUntil($condition);
        $this->findByName('download')->click();
        $validatorSearch = WebDriverBy::id('anchorClearpay');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * @param string $language
     * @param string $languageName
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     * @throws \Facebook\WebDriver\Exception\UnexpectedTagNameException
     */
    public function configureLanguagePack($language = '72', $languageName = 'Español (Spanish)')
    {
        $elementSearch = WebDriverBy::partialLinkText('Localization');
        $condition = WebDriverExpectedCondition::elementToBeClickable($elementSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findByLinkText('Localization')->click();

        $this->findById('iso_localization_pack_chosen')->click();
        $this->findByCss('.chosen-results .active-result[data-option-array-index="'. $language .'"]')->click();
        $this->findByName('submitLocalizationPack')->click();

        $languageInstallSelect = new WebDriverSelect($this->findById('PS_LANG_DEFAULT'));
        $languageInstallSelect->selectByVisibleText($languageName);
        $this->findByName('submitOptionsconfiguration')->click();
    }

    /**
     * @require loginToBackOffice
     *
     * @throws \Exception
     */
    public function getClearpayBackOffice()
    {
        $this->webDriver->get(self::PS16URL.self::BACKOFFICE_FOLDER);
        $this->findByLinkText('Modules and Services')->click();
        $this->findById('moduleQuicksearch')->clear()->sendKeys('Clearpay');
        $clearpayAnchor = $this->findById('anchorClearpay');
        $clearpayAnchorParent = $this->getParent($clearpayAnchor);
        $clearpayAnchorGrandParent = $this->getParent($clearpayAnchorParent);
        $this->moveToElementAndClick($clearpayAnchorGrandParent->findElement(
            WebDriverBy::partialLinkText('Configure')
        ));
        $verify = WebDriverBy::id('CLEARPAY_PUBLIC_KEY');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($verify);
        $this->waitUntil($condition);
    }

    /**
     * @throws \Exception
     */
    public function loginToFrontend()
    {
        $this->webDriver->get(self::PS16URL.self::COUNTRY_QUERYSTRING);
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
        $this->webDriver->get(self::PS16URL.self::COUNTRY_QUERYSTRING);
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
        $this->webDriver->get(self::PS16URL.self::COUNTRY_QUERYSTRING);
        $shoppingCart = WebDriverBy::className('shopping_cart');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated(
            $shoppingCart
        );
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        $this->findByLinkText('Carrito')->click();
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
            $this->webDriver->wait('10', 1000)->until($condition);
            $this->assertTrue((bool) $condition);
            $this->findById('company')->clear()->sendKeys($this->configuration['company']);
            $this->findById('address1')->clear()->sendKeys('av.diagonal 579');
            $this->findById('postcode')->clear()->sendKeys($this->configuration['zip']);
            $this->findById('city')->clear()->sendKeys($this->configuration['city']);
            $stateSelect = new WebDriverSelect($this->findById('id_state'));
            $stateSelect->selectByVisibleText($this->configuration['state']);
            $this->findById('phone')->clear()->sendKeys($this->configuration['phone']);
            $this->findById('phone_mobile')->clear()->sendKeys($this->configuration['phone']);
            $this->findById('dni')->clear()->sendKeys($this->configuration['dni']);
            $this->moveToElementAndClick($this->findById('submitAddress'));
            $processAddress = WebDriverBy::name('processAddress');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processAddress);
            $this->webDriver->wait('10', 1000)->until($condition);
            $this->assertTrue((bool) $condition);
        } catch (\Exception $exception) {
            $processAddress = WebDriverBy::name('processAddress');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($processAddress);
            $this->webDriver->wait('10', 1000)->until($condition);
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
        $clearpaySimulatorClearpay = WebDriverBy::className('clearpaySimulatorClearpay');
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($clearpaySimulatorClearpay);
        $this->waitUntil($condition);
        $this->assertTrue((bool)$condition);
        // this sleep is to prevent simulator js render
        sleep(5);
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
        $this->webDriver->get(self::PS16URL.self::COUNTRY_QUERYSTRING);
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
            $clearpaySimulatorClearpay = WebDriverBy::className('clearpaySimulatorClearpay');
            $condition = WebDriverExpectedCondition::presenceOfElementLocated($clearpaySimulatorClearpay);
            $this->waitUntil($condition);
            $this->assertTrue((bool)$condition);
            // this sleep is to prevent simulator js render
            sleep(5);
        }
    }

    /**
     * Verify clearpay
     *
     * @throws \Exception
     */
    public function verifyClearpay()
    {
        $clearpayCheckout = WebDriverBy::className('clearpay-checkout');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($clearpayCheckout);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);

        var_dump("Pre title---->".$this->webDriver->getTitle());
        sleep(10);
        // $this->webDriver->findElement($clearpayCheckout)->click();
        $this->webDriver->executeScript('document.querySelector(\'.clearpay-checkout\').click();');

        $condition = WebDriverExpectedCondition::titleContains(self::TITLE);
        $this->webDriver->wait()->until($condition, $this->webDriver->getCurrentURL());
        $this->assertTrue((bool)$condition, "PR32");

        var_dump("out title---->".$this->webDriver->getTitle());
        SeleniumHelper::finishForm($this->webDriver);
        sleep(10);
        var_dump("Pos title---->".$this->webDriver->getTitle());
    }
}
