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
class ClearpayPs15ControllerTest extends ClearpayPrestashopTest
{
    /**
     * log route
     */
    const LOG_FOLDER = '/index.php?fc=module&module=clearpay&controller=log&product=CLEARPAY';

    /**
     * config route
     */
    const CONFIG_FOLDER = '/index.php?fc=module&module=clearpay&controller=config&product=CLEARPAY';

    protected $configs = array(
        "TITLE",
        "SIMULATOR_DISPLAY_TYPE",
        "URL_OK",
        "URL_KO",
    );

    /**
     * Test testLogDownload
     */
    public function testLogDownload()
    {
        $logUrl = self::PS15URL.self::LOG_FOLDER.'&secret=c580df9e0b7b40c3';
        $response = Request::get($logUrl)->expects('json')->send();
        $this->assertGreaterThan(0, count($response->body));
        $this->quit();
    }

    /**
     * Test testSetConfig
     */
    public function testSetConfig()
    {
        $configUrl = self::PS15URL.self::CONFIG_FOLDER.'&secret=c580df9e0b7b40c3';
        $body = array('TITLE' => 'changed');
        $response = Request::post($configUrl)
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
        $configUrl = self::PS15URL.self::CONFIG_FOLDER.'&secret=c580df9e0b7b40c3';
        $response = Request::get($configUrl)->expects('json')->send();

        foreach ($this->configs as $config) {
            $this->assertArrayHasKey($config, (array) $response->body);
        }
        $this->quit();
    }
}