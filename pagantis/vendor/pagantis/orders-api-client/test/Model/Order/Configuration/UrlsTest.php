<?php

namespace Test\Pagantis\OrdersApiClient\Model\Order\Configuration;

use Pagantis\OrdersApiClient\Model\Order\Configuration\Urls;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class UrlsTest
 * @package Test\Pagantis\OrdersApiClient\Model\Order\Configuration
 */
class UrlsTest extends AbstractTest
{
    /**
     * Invalid URL
     */
    const VALID_URL = 'http://pagantis.com:8080//orders?order=true';

    /**
     *  Valid URL
     */
    const INVALID_URL = '://pay.es';

    /**
     * testUrlValidate
     */
    public function testUrlValidate()
    {
        $this->assertTrue(Urls::urlValidate('https://pagantis.com'));
        $this->assertTrue(Urls::urlValidate('http://pagantis.com:8080//orders?order=true'));
        $this->assertFalse(Urls::urlValidate('://google.es'));
        $this->assertFalse(Urls::urlValidate('google.es'));
        $this->assertFalse(Urls::urlValidate('google'));
    }

    /**
     * testSetOk
     */
    public function testSetOk()
    {
        $urls = new Urls();
        $urls->setOk(null);
        $this->assertNull($urls->getOk());
        $urls->setOk(self::VALID_URL);
        $this->assertEquals(self::VALID_URL, $urls->getOk());
    }

    /**
     * testSetOk
     */
    public function testSetKo()
    {
        $urls = new Urls();
        $urls->setKo(null);
        $this->assertNull($urls->getKo());
        $urls->setKo(self::VALID_URL);
        $this->assertEquals(self::VALID_URL, $urls->getKo());
    }

    /**
     * testSetOk
     */
    public function testSetCancel()
    {
        $urls = new Urls();
        $urls->setCancel(null);
        $this->assertNull($urls->getCancel());
        $urls->setCancel(self::VALID_URL);
        $this->assertEquals(self::VALID_URL, $urls->getCancel());
    }

    /**
 * testSetOk
 */
    public function testSetAuthorizedNotificationCallback()
    {
        $urls = new Urls();
        $urls->setAuthorizedNotificationCallback(null);
        $this->assertNull($urls->getAuthorizedNotificationCallback());
        $urls->setAuthorizedNotificationCallback(self::VALID_URL);
        $this->assertEquals(self::VALID_URL, $urls->getAuthorizedNotificationCallback());
    }

    /**
     * testSetOk
     */
    public function testSetRejectedNotificationCallback()
    {
        $urls = new Urls();
        $urls->setRejectedNotificationCallback(null);
        $this->assertNull($urls->getRejectedNotificationCallback());
        $urls->setRejectedNotificationCallback(self::VALID_URL);
        $this->assertEquals(self::VALID_URL, $urls->getRejectedNotificationCallback());
    }

    /**
     * testSetOk
     */
    public function testSetInvalidatedNotificationCallback()
    {
        $urls = new Urls();
        $urls->setInvalidatedNotificationCallback(null);
        $this->assertNull($urls->getInvalidatedNotificationCallback());
        $urls->setInvalidatedNotificationCallback(self::VALID_URL);
        $this->assertEquals(self::VALID_URL, $urls->getInvalidatedNotificationCallback());
    }
}
