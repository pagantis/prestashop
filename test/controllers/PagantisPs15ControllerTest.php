<?php

namespace Test;

use Httpful\Request;
use Httpful\Mime;

/**
 * Class ControllerTest
 * @package Test
 *
 * @group prestashop15controller
 */
class PagantisPs15ControllerTest extends PagantisPrestashopTest
{
    /**
     * log route
     */
    const LOG_FOLDER = '/index.php?fc=module&module=pagantis&controller=log';

    /**
     * config route
     */
    const CONFIG_FOLDER = '/index.php?fc=module&module=pagantis&controller=config';

    protected $configs = array(
        "PAGANTIS_TITLE",
        "PAGANTIS_SIMULATOR_DISPLAY_TYPE",
        "PAGANTIS_SIMULATOR_DISPLAY_SKIN",
        "PAGANTIS_SIMULATOR_DISPLAY_POSITION",
        "PAGANTIS_SIMULATOR_START_INSTALLMENTS",
        "PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR",
        "PAGANTIS_SIMULATOR_DISPLAY_CSS_POSITION",
        "PAGANTIS_SIMULATOR_CSS_PRICE_SELECTOR",
        "PAGANTIS_SIMULATOR_CSS_QUANTITY_SELECTOR",
        "PAGANTIS_FORM_DISPLAY_TYPE",
        "PAGANTIS_DISPLAY_MIN_AMOUNT",
        "PAGANTIS_DISPLAY_MAX_AMOUNT",
        "PAGANTIS_URL_OK",
        "PAGANTIS_URL_KO",
    );

    /**
     * Test testLogDownload
     */
    public function testLogDownload()
    {
        $logUrl = self::PS15URL.self::LOG_FOLDER.'&secret=3af9065648bf1970';
        $response = Request::get($logUrl)->expects('json')->send();
        $this->assertEquals(3, count($response->body));
        $this->quit();
    }

    /**
     * Test testSetConfig
     */
    public function testSetConfig()
    {
        $configUrl = self::PS15URL.self::CONFIG_FOLDER.'&secret=3af9065648bf1970';
        $body = array('PAGANTIS_TITLE' => 'changed');
        $response = Request::post($configUrl)
            ->body($body, Mime::FORM)
            ->expectsJSON()
            ->send();
        $this->assertEquals('changed', $response->body->PAGANTIS_TITLE);
        $this->quit();
    }

    /**
     * Test testGetConfig
     */
    public function testGetConfigs()
    {
        $configUrl = self::PS15URL.self::CONFIG_FOLDER.'&secret=3af9065648bf1970';
        $response = Request::get($configUrl)->expects('json')->send();

        foreach ($this->configs as $config) {
            $this->assertArrayHasKey($config, (array) $response->body);
        }
        $this->quit();
    }
}