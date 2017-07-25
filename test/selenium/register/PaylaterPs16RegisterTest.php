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
        try {
            $this->createAccount();
        } catch (\Exception $exception) {
        }
        $this->login();
        $this->quit();
    }

    /**
     * Test go to login
     */
    public function goToLogin()
    {
        $this->webDriver->get(self::PS16URL);
        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('header_user_info')
            )
        );

        $this->findByClass('header_user_info')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('SubmitCreate')
            )
        );
    }

    /**
     * Create Account:
     */
    public function createAccount()
    {
        $this->findById('email_create')->sendKeys($this->configuration['email']);
        $this->findById('SubmitCreate')->click();
        $this->webDriver->wait(10, 2000)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('submitAccount')
            )
        );
        //Fillup form:
        $this->findById('uniform-id_gender1')->click();
        $this->findByName('customer_firstname')->sendKeys($this->configuration['firstname']);
        $this->findByName('customer_lastname')->sendKeys($this->configuration['lastname']);
        $this->findByName('passwd')->sendKeys($this->configuration['password']);

        $this->webDriver->executeScript('document.getElementById("submitAccount").click();');

        sleep(2);
    }

    /**
     * Login to the site
     */
    public function login()
    {
        $this->webDriver->get(self::PS16URL);
        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('header_user_info')
            )
        );

        $this->findByClass('header_user_info')->click();
        $this->findByName('email')->sendKeys($this->configuration['email']);
        $this->findByName('passwd')->sendKeys($this->configuration['password']);
        $this->findById('SubmitLogin')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('header_user_info')
            )
        );

        $this->assertContains(
            $this->configuration['firstname'],
            $this->findByClass('header_user_info')->getText()
        );
    }
}
