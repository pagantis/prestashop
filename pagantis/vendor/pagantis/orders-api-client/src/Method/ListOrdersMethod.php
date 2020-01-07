<?php

namespace Pagantis\OrdersApiClient\Method;

use Httpful\Http;
use Httpful\Request;
use Httpful\Response;
use Pagantis\OrdersApiClient\Model\Order;

/**
 * Class ListOrdersMethod
 *
 * @package Pagantis\OrdersApiClient\Method
 */
class ListOrdersMethod extends AbstractMethod
{
    /**
     * Get Order Endpoint
     */
    const ENDPOINT = '/orders';

    /**
     * @var array $queryParameters
     */
    protected $queryParameters;

    /**
     * @param array $queryParameters
     *
     * @return $this
     */
    public function setQueryParameters(array $queryParameters)
    {
        $this->queryParameters = $queryParameters;

        return $this;
    }

    /**
     * @return array|bool
     *
     * @throws \Exception
     */
    public function getOrders()
    {
        $response = $this->getResponse();
        if ($response instanceof Response) {
            $responseBody = $response->body;
            $orders = array();
            foreach ($responseBody as $responseOrder) {
                $order = new Order();
                $order->import($responseOrder);
                $orders[] = $order;
            }

            return $orders;
        }

        return false;
    }

    /**
     * @return $this|AbstractMethod
     * @throws \Httpful\Exception\ConnectionErrorException
     * @throws \Pagantis\OrdersApiClient\Exception\HttpException
     */
    public function call()
    {
        $this->prepareRequest();
        return $this->setResponse($this->request->send());
    }

    /**
     * prepareRequest
     */
    public function prepareRequest()
    {
        if (!$this->request instanceof Request) {
            $this->request = $this->getRequest()
                ->method(Http::GET)
                ->uri(
                    $this->apiConfiguration->getBaseUri()
                    . self::ENDPOINT
                    . $this->addGetParameters($this->queryParameters)
                )
            ;
        }
    }
}
