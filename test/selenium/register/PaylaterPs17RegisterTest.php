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
        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('user-info')
            )
        );

        $this->findByClass('user-info')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('btn-primary')
            )
        );
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

        //Fillup form:
        $this->findByClass('custom-radio')->click();
        $this->findByName('firstname')->sendKeys($this->configuration['firstname']);
        $this->findByName('lastname')->sendKeys($this->configuration['lastname']);
        $this->findByName('email')->sendKeys($this->configuration['email']);
        $this->findByName('password')->sendKeys($this->configuration['password']);
        $this->findByName('birthday')->sendKeys($this->configuration['birthdate']);

        $this->webDriver->executeScript('document.getElementById(\'customer-form\').submit();');

        sleep(1);
        $this->findByClass('user-info')->click();
    }

    /**
     * Login to the site
     */
    public function login()
    {
        $this->webDriver->get(self::PS17URL.'/index.php?controller=authentication&back=my-account');
        sleep(1);
        $this->findByName('email')->sendKeys($this->configuration['email']);
        $this->findByName('password')->sendKeys($this->configuration['password']);
        $this->webDriver->executeScript('document.getElementById(\'login-form\').submit();');
    }
}
