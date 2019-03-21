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
 * Class PagantisPrestashopTest
 * @package Test\Selenium
 */
abstract class PagantisPrestashopTest extends TestCase
{
    const PS17URL = 'http://prestashop17-test.docker:8017';
    const PS16URL = 'http://prestashop16-test.docker:8016';
    const PS15URL = 'http://prestashop15-test.docker:8015';

    const BACKOFFICE_FOLDER = '/adminTest';

    /**
     * @var array
     */
    protected $configuration = array(
        'username'      => 'demo@prestashop.com',
        'password'      => 'prestashop_demo',
        'publicKey'     => 'tk_b406b913ce3b477a8f59bbc4',
        'secretKey'     => 'cf72f300a2994e42',
        'birthdate'     => '05/05/1989',
        'firstname'     => 'Jøhn',
        'lastname'      => 'Dōès',
        'email'         => 'john_doe_testing@pagantis.com',
        'company'       => 'Pagantis SA',
        'zip'           => '08023',
        'city'          => 'Barcelona',
        'phone'         => '600123123',
        'dni'           => '65592819Q',
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
        $this->configuration['email'] = date('ymd') . '@pagantis.com';
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
        $this->webDriver = PagantisWebDriver::create(
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
            'PAGANTIS_TITLE' => '\'Instant Financing\'',
            'PAGANTIS_SIMULATOR_DISPLAY_TYPE' => 'pmtSDK.simulator.types.SIMPLE',
            'PAGANTIS_SIMULATOR_DISPLAY_SKIN' => 'pmtSDK.simulator.skins.BLUE ',
            'PAGANTIS_SIMULATOR_DISPLAY_POSITION' => 'hookDisplayProductButtons',
            'PAGANTIS_SIMULATOR_START_INSTALLMENTS' => '3',
            'PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR' => 'default',
            'PAGANTIS_SIMULATOR_DISPLAY_CSS_POSITION' => 'pmtSDK.simulator.positions.INNER',
            'PAGANTIS_SIMULATOR_CSS_PRICE_SELECTOR' => 'default',
            'PAGANTIS_SIMULATOR_CSS_QUANTITY_SELECTOR' => 'default',
            'PAGANTIS_FORM_DISPLAY_TYPE' => '0',
            'PAGANTIS_DISPLAY_MIN_AMOUNT' => '1',
            'PAGANTIS_URL_OK' => '',
            'PAGANTIS_URL_KO' => '',
        );
    }

    /**
     * @param array  $properties
     * @param string $psVersion
     */
    public function saveDotEnvFile($properties = array(), $psVersion = '15')
    {
        $file = '';
        foreach ($properties as $key => $value) {
            $file .= $key . '=' . $value . PHP_EOL;
        }

        $fp = fopen('/tmp/.env', 'w');
        fwrite($fp, $file);
        fclose($fp);

        $command = 'docker cp /tmp/.env prestashop' . $psVersion . ':/var/www/html/modules/pagantis/.env 2>&1';
        $execResult = exec($command);
        $this->assertEmpty($execResult);

        $command = 'docker-compose exec -T prestashop' . $psVersion.' chmod 777 /var/www/html/modules/pagantis/.env';
        $execResult = exec($command);
        $this->assertEmpty($execResult);

        $command = 'docker-compose exec -T prestashop' . $psVersion.' chown www-data. /var/www/html/modules/pagantis/.env';
        $execResult = exec($command);
        $this->assertEmpty($execResult);
    }
}
