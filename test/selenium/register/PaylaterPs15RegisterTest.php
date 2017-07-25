<?php

namespace Test\Selenium\Install;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\PaylaterPrestashopTest;

/**
 * Class PaylaterPs15RegisterTest
 * @package Test\Selenium\Basic
 *
 * @group prestashop15register
 */
class PaylaterPs15RegisterTest extends PaylaterPrestashopTest
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
        $this->webDriver->get(self::PS15URL);
        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('header_user_info')
            )
        );

        $this->findById('header_user_info')->click();

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
        $this->findById('id_gender1')->click();
        $this->findByName('customer_firstname')->sendKeys($this->configuration['firstname']);
        $this->findByName('customer_lastname')->sendKeys($this->configuration['lastname']);
        $this->findByName('passwd')->sendKeys($this->configuration['password']);

        $this->findById('days')->findElement(
            WebDriverBy::cssSelector("option[value='1']")
        )->click();
        $this->findById('months')->findElement(
            WebDriverBy::cssSelector("option[value='2']")
        )->click();
        $this->findById('years')->findElement(
            WebDriverBy::cssSelector("option[value='1990']")
        )->click();

        $this->webDriver->executeScript('document.getElementById("submitAccount").click();');

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('logout')
            )
        );

        $this->findByClass('logout')->click();
    }

    /**
     * Login to the site
     */
    public function login()
    {
        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('login')
            )
        );

        $this->findByClass('login')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::id('SubmitLogin')
            )
        );

        $this->findById('email')->sendKeys($this->configuration['email']);
        $this->findById('passwd')->sendKeys($this->configuration['password']);
        $this->findById('SubmitLogin')->click();

        $this->webDriver->wait(5, 500)->until(
            WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::className('logout')
            )
        );

        $this->assertContains(
            $this->configuration['firstname'],
            $this->findById('header_user_info')->getText()
        );
    }
}
