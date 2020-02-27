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
        "ENCUOTAS_TITLE",
        "ENCUOTAS_SIMULATOR_DISPLAY_TYPE",
        "ENCUOTAS_SIMULATOR_DISPLAY_SKIN",
        "ENCUOTAS_SIMULATOR_DISPLAY_POSITION",
        "ENCUOTAS_SIMULATOR_START_INSTALLMENTS",
        "ENCUOTAS_SIMULATOR_CSS_POSITION_SELECTOR",
        "ENCUOTAS_SIMULATOR_DISPLAY_CSS_POSITION",
        "ENCUOTAS_SIMULATOR_CSS_PRICE_SELECTOR",
        "ENCUOTAS_SIMULATOR_CSS_QUANTITY_SELECTOR",
        "ENCUOTAS_FORM_DISPLAY_TYPE",
        "ENCUOTAS_DISPLAY_MIN_AMOUNT",
        "ENCUOTAS_DISPLAY_MAX_AMOUNT",
        "ENCUOTAS_URL_OK",
        "ENCUOTAS_URL_KO",
    );

    /**
     * Test testLogDownload
     */
    public function testLogDownload()
    {
        $logUrl = self::PS15URL.self::LOG_FOLDER.'&secret='.$this->configuration['secretKey'];
        $response = Request::get($logUrl)->expects('json')->send();
        $this->assertEquals(3, count($response->body));
        $this->quit();
    }

    /**
     * Test testSetConfig
     */
    public function testSetConfig()
    {
        $configUrl = self::PS15URL.self::CONFIG_FOLDER.'&secret='.$this->configuration['secretKey'];
        $body = array('ENCUOTAS_TITLE' => 'changed');
        $response = Request::post($configUrl)
            ->body($body, Mime::FORM)
            ->expectsJSON()
            ->send();
        $this->assertEquals('changed', $response->body->ENCUOTAS_TITLE);
        $this->quit();
    }

    /**
     * Test testGetConfig
     */
    public function testGetConfigs()
    {
        $configUrl = self::PS15URL.self::CONFIG_FOLDER.'&secret='.$this->configuration['secretKey'];
        $response = Request::get($configUrl)->expects('json')->send();

        foreach ($this->configs as $config) {
            $this->assertArrayHasKey($config, (array) $response->body);
        }
        $this->quit();
    }
}