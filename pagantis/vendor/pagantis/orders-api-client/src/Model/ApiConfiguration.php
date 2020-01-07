<?php

namespace Pagantis\OrdersApiClient\Model;

use Pagantis\OrdersApiClient\Exception\ClientException;
use Pagantis\OrdersApiClient\Model\Order\Configuration\Urls;

/**
 * Class ApiConfiguration
 * @package Pagantis\OrdersApiClient\Model
 */
class ApiConfiguration extends AbstractModel
{
    /**
     * Base Production URL for API calls
     */
    const BASE_URI = 'https://api.pagamastarde.com/v2';

    /**
     * Base Sandbox URL for API calls
     */
    const SANDBOX_BASE_URI = 'https://api-stg.pagamastarde.com/v2';

    /**
     * Private key for API calls
     *
     * @var string $privateKey
     */
    protected $privateKey;

    /**
     * Public key for API calls
     *
     * @var string $publicKey
     */
    protected $publicKey;

    /**
     * SandBox url should be specified here
     *
     * @var string $baseUri
     */
    protected $baseUri;

    /**
     * Headers send to the API
     *
     * @var array
     */
    protected $headers = array();

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param string $privateKey
     *
     * @return ApiConfiguration
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     *
     * @return ApiConfiguration
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * @param $baseUri
     *
     * @return $this
     * @throws ClientException
     */
    public function setBaseUri($baseUri)
    {
        if (Urls::urlValidate($baseUri)) {
            $this->baseUri = $baseUri;
            return $this;
        }

        throw new ClientException('Invalid base URL on the ApiConfiguration setter');
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return ApiConfiguration
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param $header
     * @return ApiConfiguration
     */
    public function addHeader($header)
    {
        $this->headers[] = $header;

        return $this;
    }
}
