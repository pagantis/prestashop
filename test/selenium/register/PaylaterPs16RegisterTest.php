<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs16RegisterTest
 * @package Test\Selenium\Basic
 *
 * @group prestashop16register
 */
class PaylaterPs16RegisterTest extends PaylaterPrestashopTest
{
    /**
     * Register and login test
     */
    public function testRegisterAndLogin()
    {
        $this->goToLogin();
        $this->createAccount();
        $this->login();
        $this->quit();
    }

    /**
     * Test go to login
     */
    public function goToLogin()
    {
        $this->webDriver->get(self::PS16URL);
        $loginButtonSearch = WebDriverBy::className('login');
        $condition = WebDriverExpectedCondition::elementToBeClickable($loginButtonSearch);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($loginButtonSearch)->click();
        $verifyElement = WebDriverBy::id('SubmitLogin');
        $condition = WebDriverExpectedCondition::elementToBeClickable($verifyElement);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * Create Account:
     */
    public function createAccount()
    {
        $this->findById('email_create')->sendKeys($this->configuration['email']);
        $this->findById('SubmitCreate')->click();
        try {
            $submitAccountSearch = WebDriverBy::id('customer_firstname');
            $condition = WebDriverExpectedCondition::visibilityOfElementLocated($submitAccountSearch);
            $this->waitUntil($condition);
            $this->assertTrue((bool) $condition);
        } catch (\Exception $exception) {
            return true;
        }
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
     * Login to the site
     */
    public function login()
    {
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
}
