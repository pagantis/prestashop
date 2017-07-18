<?php

namespace Test\Selenium;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use PHPUnit\Framework\TestCase;

/**
 * Class PaylaterPrestashopTestCase
 * @package Test\Selenium
 */
abstract class PaylaterPrestashopTestCase extends TestCase
{
    const PS17URL = 'http://prestashop17:8017';

    const BACKOFFICE_FOLDER = '/adminTest';

    /**
     * @var array
     */
    protected $configuration = [
        'username' => 'demo@prestashop.com',
        'password' => 'prestashop_demo',
    ];

    /**
     * @var RemoteWebDriver
     */
    protected $webDriver;

    /**
     * Configure selenium
     */
    protected function setUp()
    {
        $capabilities = array(WebDriverCapabilityType::BROWSER_NAME => 'chrome');
        $this->webDriver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities);
    }
}
