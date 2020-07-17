<?php

namespace Test;

use Httpful\Request;
use Httpful\Mime;

/**
 * Class ControllerTest
 * @package Test
 *
 * @group prestashop16controller
 */
class PagantisPs16ControllerTest extends PagantisPrestashopTest
{
    /**
     * log route
     */
    const LOG_FOLDER = '/index.php?fc=module&module=pagantis&controller=log&limit=100&from=20200101&product=p12x';

    /**
     * config route
     */
    const CONFIG_FOLDER = '/index.php?fc=module&module=pagantis&controller=config&product=p12x';

    protected $configs = array(
        "TITLE",
        "SIMULATOR_DISPLAY_TYPE",
        "SIMULATOR_DISPLAY_SKIN",
        "SIMULATOR_START_INSTALLMENTS",
        "SIMULATOR_CSS_POSITION_SELECTOR",
        "SIMULATOR_DISPLAY_CSS_POSITION",
        "SIMULATOR_CSS_PRICE_SELECTOR",
        "SIMULATOR_CSS_QUANTITY_SELECTOR",
        "FORM_DISPLAY_TYPE",
        "DISPLAY_MIN_AMOUNT",
        "DISPLAY_MAX_AMOUNT",
        "URL_OK",
        "URL_KO",
    );

    /**
     * Test testLogDownload
     */
    public function testLogDownload()
    {
        $logUrl = self::PS16URL.self::LOG_FOLDER.'&secret=13e3ca35bdae432d';
        $response = Request::get($logUrl)->expects('json')->send();
        $this->assertGreaterThan(0, count($response->body));
        $this->quit();
    }

    /**
     * Test testSetConfig
     */
    public function testSetConfig()
    {
        $notifyUrl = self::PS16URL.self::CONFIG_FOLDER.'&secret=13e3ca35bdae432d';
        $body = array('TITLE' => 'changed');
        $response = Request::post($notifyUrl)
            ->body($body, Mime::FORM)
            ->expectsJSON()
            ->send();
        $this->assertEquals('changed', $response->body->TITLE);
        $this->quit();
    }

    /**
     * Test testGetConfig
     */
    public function testGetConfigs()
    {
        $notifyUrl = self::PS16URL.self::CONFIG_FOLDER.'&secret=13e3ca35bdae432d';
        $response = Request::get($notifyUrl)->expects('json')->send();

        foreach ($this->configs as $config) {
            $this->assertArrayHasKey($config, (array) $response->body);
        }
        $this->quit();
    }
}