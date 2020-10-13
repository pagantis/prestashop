<?php

namespace Test;

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

/**
 * Class ClearpayPrestashopTest
 * @package Test\Selenium
 */
abstract class ClearpayPrestashopTest extends TestCase
{
    const PS17URL = 'http://prestashop17-test.docker:8017';
    const PS16URL = 'http://prestashop16-test.docker:8016';
    const PS15URL = 'http://prestashop15-test.docker:8015';

    const COUNTRY_QUERYSTRING = '?id_lang=3';
    const COUNTRY_QUERYSTRING_17 = '?id_lang=2';

    // DEV
    // const PS17URL = 'http://prestashop17-dev.docker:8018';
    // const PS16URL = 'http://prestashop16-dev.docker:8019';
    // const PS15URL = 'http://prestashop15-dev.docker:8020';

    const BACKOFFICE_FOLDER = '/adminTest';

    const TITLE = 'Clearpay';

    /**
     * @var array
     */
    protected $configuration = array(
        'username'      => 'demo@prestashop.com',
        'password'      => 'prestashop_demo',
        'publicKey'     => 'tk_05f3993ef51d41209c52eac7',
        'secretKey'     => 'c580df9e0b7b40c3',
        'birthdate'     => '05/05/1989',
        'firstname'     => 'Jøhn',
        'lastname'      => 'Dōès',
        'email'         => 'john_doe_testing@clearpay.com',
        'company'       => 'Clearpay SA',
        'zip'           => '08023',
        'city'          => 'Barcelona',
        'state'         => 'Barcelona',
        'phone'         => '600123123',
        'dni'           => '09575045F',
        'extra'         => 'Free Finance',
        'confirmationMessage' => 'Great, you have completed your purchase',
    );

    /**
     * WooCommerce constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        $faker = Factory::create();
        $this->configuration['dni'] = $this->getDNI();
        $this->configuration['birthdate'] =
            $faker->numberBetween(1, 28) . '/' .
            $faker->numberBetween(1, 12). '/1975'
        ;
        $this->configuration['firstname'] = $faker->firstName;
        $this->configuration['lastname'] = $faker->lastName . ' ' . $faker->lastName;
        $this->configuration['company'] = $faker->company;
        $this->configuration['zip'] = '28'.$faker->randomNumber(3, true);
        $this->configuration['street'] = $faker->streetAddress;
        $this->configuration['phone'] = '6' . $faker->randomNumber(8);
        $this->configuration['email'] = date('ymd') . '@clearpay.com';
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @return string
     */
    protected function getDNI()
    {
        $dni = '0000' . rand(pow(10, 4-1), pow(10, 4)-1);
        $value = (int) ($dni / 23);
        $value *= 23;
        $value= $dni - $value;
        $letter= "TRWAGMYFPDXBNJZSQVHLCKEO";
        $dniLetter= substr($letter, $value, 1);
        return $dni.$dniLetter;
    }

    /**
     * @var RemoteWebDriver
     */
    protected $webDriver;

    /**
     * Configure selenium
     */
    protected function setUp()
    {
        $this->webDriver = ClearpayWebDriver::create(
            'http://localhost:4444/wd/hub',
            DesiredCapabilities::chrome(),
            120000,
            120000
        );
    }

    /**
     * @param $name
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findByName($name)
    {
        return $this->webDriver->findElement(WebDriverBy::name($name));
    }

    /**
     * @param $id
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findById($id)
    {
        return $this->webDriver->findElement(WebDriverBy::id($id));
    }

    /**
     * @param $className
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findByClass($className)
    {
        return $this->webDriver->findElement(WebDriverBy::className($className));
    }

    /**
     * @param $css
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findByCss($css)
    {
        return $this->webDriver->findElement(WebDriverBy::cssSelector($css));
    }

    /**
     * @param $link
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebElement
     */
    public function findByLinkText($link)
    {
        return $this->webDriver->findElement(WebDriverBy::partialLinkText($link));
    }

    /**
     * @param WebDriverExpectedCondition $condition
     * @return mixed
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function waitUntil(WebDriverExpectedCondition $condition)
    {
        return $this->webDriver->wait()->until($condition);
    }

    /**
     * @param WebDriverElement $element
     *
     * @return WebDriverElement
     */
    public function moveToElementAndClick(WebDriverElement $element)
    {
        $action = new WebDriverActions($this->webDriver);
        $action->moveToElement($element);
        $action->click($element);
        $action->perform();

        return $element;
    }

    /**
     * @param WebDriverElement $element
     *
     * @return WebDriverElement
     */
    public function getParent(WebDriverElement $element)
    {
        return $element->findElement(WebDriverBy::xpath(".."));
    }

    /**
     * Quit browser
     */
    protected function quit()
    {
        $this->webDriver->quit();
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return array(
            'TITLE' => '\'Instant Financing\'',
            'SIMULATOR_DISPLAY_TYPE' => 'pgSDK.simulator.types.SIMPLE',
            'SIMULATOR_DISPLAY_SKIN' => 'pgSDK.simulator.skins.BLUE ',
            'SIMULATOR_START_INSTALLMENTS' => '3',
            'SIMULATOR_CSS_POSITION_SELECTOR' => 'default',
            'SIMULATOR_DISPLAY_CSS_POSITION' => 'pgSDK.simulator.positions.INNER',
            'SIMULATOR_CSS_PRICE_SELECTOR' => 'default',
            'SIMULATOR_CSS_QUANTITY_SELECTOR' => 'default',
            'FORM_DISPLAY_TYPE' => '0',
            'CLEARPAY_MIN_AMOUNT' => '1',
            'CLEARPAY_MAX_AMOUNT' => '0',
            'URL_OK' => '',
            'URL_KO' => '',
        );
    }
}
