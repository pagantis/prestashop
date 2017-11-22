<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs17RegisterTest
 * @package Test\Selenium\Basic
 *
 * @group prestashop17register
 */
class PaylaterPs17RegisterTest extends PaylaterPrestashopTest
{
    /**
     * Register and login test
     */
    public function testRegisterAndLogin()
    {
        $this->goToLogin();
        $this->goToCreateAccount();
        $this->createAccount();
        $this->login();
        $this->quit();
    }

    /**
     * Test go to login
     */
    public function goToLogin()
    {
        $this->webDriver->get(self::PS17URL);
        $loginButtonSearch = WebDriverBy::className('user-info');
        $condition = WebDriverExpectedCondition::elementToBeClickable($loginButtonSearch);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($loginButtonSearch)->click();
        $verifyElement = WebDriverBy::name('email');
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($verifyElement);
        $this->webDriver->wait()->until($condition);
        $this->assertTrue((bool) $condition);
    }

    /**
     * Create account goto
     */
    public function goToCreateAccount()
    {
        $this->findByClass('no-account')->click();
        $this->webDriver->wait(2, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('btn-primary')
            )
        );
    }

    /**
     * Create Account:
     */
    public function createAccount()
    {
        $this->webDriver->get(self::PS17URL);
        $this->findByClass('user-info')->click();
        $this->findByClass('no-account')->click();
        $this->findByClass('custom-radio')->click();
        $this->findByName('firstname')->sendKeys($this->configuration['firstname']);
        $this->findByName('lastname')->sendKeys($this->configuration['lastname']);
        $this->findByName('email')->sendKeys($this->configuration['email'].'123345');
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
     * Login to the site
     */
    public function login()
    {
        $this->goToLogin();
        $submitLoginButtonSearch = WebDriverBy::name('email');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($submitLoginButtonSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->findByName('email')->sendKeys($this->configuration['email']);
        $this->findByName('password')->sendKeys($this->configuration['password']);
        $this->findById('login-form')->submit();
        $logoutButtonSearch = WebDriverBy::className('logout');
        $condition = WebDriverExpectedCondition::elementToBeClickable($logoutButtonSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
    }
}
