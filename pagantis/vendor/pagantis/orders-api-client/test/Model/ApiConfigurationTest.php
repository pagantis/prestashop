<?php

namespace Test\Pagantis\OrdersApiClient\Model;

use Pagantis\OrdersApiClient\Model\ApiConfiguration;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class ApiConfigurationTest
 *
 * @package Test\Pagantis\OrdersApiClient\Model
 */
class ApiConfigurationTest extends AbstractTest
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
     * Base URL for API calls
     */
    const BASE_URI = 'https://api.pagamastarde.com/v2';

    /**
     * Base URL for API calls
     */
    const SANDBOX_BASE_URI = 'https://api-stg.pagamastarde.com/v2';

    /**
     * testConstantsNotChange
     */
    public function testConstantsNotChange()
    {
        $this->assertEquals(self::BASE_URI, ApiConfiguration::BASE_URI);
        $this->assertEquals(self::SANDBOX_BASE_URI, ApiConfiguration::SANDBOX_BASE_URI);
    }

    /**
     * testSetBaseUrl
     *
     * @expectedException \Pagantis\OrdersApiClient\Exception\ClientException
     */
    public function testSetBaseUrl()
    {
        $apiConfiguration = new ApiConfiguration();
        $apiConfiguration->setBaseUri(self::VALID_URL);
        $this->assertEquals(self::VALID_URL, $apiConfiguration->getBaseUri());
        $apiConfiguration->setBaseUri(self::INVALID_URL);
    }
}
