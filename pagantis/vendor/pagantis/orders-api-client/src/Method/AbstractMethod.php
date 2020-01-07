<?php

namespace Pagantis\OrdersApiClient\Method;

use Httpful\Mime;
use Httpful\Request;
use Httpful\Response;
use Pagantis\OrdersApiClient\Exception\HttpException;
use Pagantis\OrdersApiClient\Model\ApiConfiguration;

/**
 * Class AbstractMethod
 *
 * @package Pagantis\OrdersApiClient\Method
 */
abstract class AbstractMethod implements MethodInterface
{
    const SLASH = '/';

    /**
     * @var ApiConfiguration $apiConfiguration
     */
    protected $apiConfiguration;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Request
     */
    protected $request;

    /**
     * AbstractMethod constructor.
     *
     * @param ApiConfiguration $apiConfiguration
     */
    public function __construct(ApiConfiguration $apiConfiguration)
    {
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * @return bool|Response
     */
    public function getResponse()
    {
        if ($this->response instanceof Response) {
            return $this->response;
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getResponseAsJson()
    {
        $response = $this->getResponse();
        if ($response instanceof Response) {
            return $response->raw_body;
        }

        return false;
    }

    /**
     * @param $array
     *
     * @return string
     */
    protected function addGetParameters($array)
    {
        $query = '';
        if (is_array($array)) {
            $query = http_build_query(array_filter($array));
        }

        return empty($query) ? '' : '?' . $query;
    }

    /**
     * @param      $code
     * @param null $message
     *
     * @throws HttpException
     */
    protected function parseHttpException($code, $message = null)
    {
        if (!in_array($code, array(HttpException::HTTP_UNPROCESSABLE_ENTITY, HttpException::HTTP_CONFLICT))) {
            $message = null;
        }

        $objHttpException = new HttpException($code, $message);
        $status = $objHttpException->getStatus();

        if (!array_key_exists($code, $status)) {
            throw new HttpException(HttpException::HTTP_INTERNAL_SERVER_ERROR);
        }

        throw $objHttpException;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return Request::init()
            ->expects(Mime::JSON)
            ->authenticateWithBasic($this->apiConfiguration->getPublicKey(), $this->apiConfiguration->getPrivateKey())
            ->timeoutIn(30)
            ->addHeaders($this->apiConfiguration->getHeaders())
            ;
    }

    /**
     * @param Response $response
     *
     * @return $this
     * @throws HttpException
     */
    protected function setResponse(Response $response)
    {
        if (!$response->hasErrors()) {
            $this->response = $response;
            return $this;
        }

        return $this->parseHttpException($response->code, $response->raw_body);
    }
}
