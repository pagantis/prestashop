<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs17RegisterTest
 * @package Test\Selenium\Basic
 *
 * @group ps17register
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
        try {
            $this->webDriver->get(self::PS17URL);
            $this->findByClass('user-info')->click();
            $this->webDriver->wait(2, 500)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::name('submitLogin')
                )
            );
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * Create account goto
     */
    public function goToCreateAccount()
    {
        try {
            $this->findByClass('no-account')->click();
            $this->webDriver->wait(2, 500)->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::name('submitCreate')
                )
            );
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * Create Account:
     */
    public function createAccount()
    {
        try {
            $this->webDriver->get(self::PS17URL);
            $this->findByClass('user-info')->click();
            $this->findByClass('no-account')->click();

            //Fillup form:
            $this->findByClass('custom-radio')->click();
            $this->findByName('firstname')->sendKeys($this->configuration['firstname']);
            $this->findByName('lastname')->sendKeys($this->configuration['lastname']);
            $this->findByName('email')->sendKeys($this->configuration['email']);
            $this->findByName('password')->sendKeys($this->configuration['password']);
            $this->findByName('birthday')->sendKeys($this->configuration['birthdate']);

            $this->webDriver->executeScript('document.getElementById(\'customer-form\').submit();');

            sleep(10);
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * Login to the site
     */
    public function login()
    {
        try {
            $this->webDriver->get(self::PS17URL);
            $this->goToLogin();
            $this->findByName('email')->sendKeys($this->configuration['email']);
            $this->findByName('password')->sendKeys($this->configuration['password']);
            $this->webDriver->executeScript('document.getElementById(\'login-form\').submit();');
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }
}
