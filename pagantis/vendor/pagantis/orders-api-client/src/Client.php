<?php

namespace Pagantis\OrdersApiClient;

use Httpful\Exception\ConnectionErrorException;
use Pagantis\OrdersApiClient\Method\ConfirmOrderMethod;
use Pagantis\OrdersApiClient\Method\CreateOrderMethod;
use Pagantis\OrdersApiClient\Method\GetOrderMethod;
use Pagantis\OrdersApiClient\Method\ListOrdersMethod;
use Pagantis\OrdersApiClient\Method\RefundOrderMethod;
use Pagantis\OrdersApiClient\Model\ApiConfiguration;
use Pagantis\OrdersApiClient\Model\Order;

/**
 * Class Client
 *
 * @package Pagantis/OrdersApiClient
 */
class Client
{
    /**
     * @var ApiConfiguration
     */
    protected $apiConfiguration;

    /**
     * Client constructor.
     *
     * @param       $publicKey
     * @param       $privateKey
     * @param null  $baseUri
     * @param array $headers
     *
     * @throws ConnectionErrorException
     * @throws Exception\ClientException
     */
    public function __construct($publicKey, $privateKey, $baseUri = null, $headers = array())
    {
        if (!function_exists("curl_init")) {
            throw new ConnectionErrorException("Curl module is not available on this system");
        }

        $apiConfiguration = new ApiConfiguration();
        $apiConfiguration
            ->setBaseUri($baseUri ? $baseUri : ApiConfiguration::BASE_URI)
            ->setPrivateKey($privateKey)
            ->setPublicKey($publicKey)
            ->setHeaders($headers)
        ;
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * @param Order $order
     * @param bool  $asJson
     *
     * @return bool|Order|string
     *
     * @throws \Exception
     */
    public function createOrder(Order $order, $asJson = false)
    {
        $createOrderMethod = new CreateOrderMethod($this->apiConfiguration);
        $createOrderMethod->setOrder($order);
        if ($asJson) {
            return $createOrderMethod->call()->getResponseAsJson();
        }

        return $createOrderMethod->call()->getOrder();
    }

    /**
     * @param      $orderId
     * @param bool $asJson
     *
     * @return bool|Order|string
     *
     * @throws \Exception
     */
    public function getOrder($orderId, $asJson = false)
    {
        $getOrderMethod = new GetOrderMethod($this->apiConfiguration);
        $getOrderMethod->setOrderId($orderId);
        if ($asJson) {
            return $getOrderMethod->call()->getResponseAsJson();
        }

        return $getOrderMethod->call()->getOrder();
    }

    /**
     * listOrders
     *
     * @param array|null $queryString
     * @param bool       $asJson
     *
     * @return array|bool|string
     *
     * @throws \Exception
     */
    public function listOrders(array $queryString = null, $asJson = false)
    {
        $listOrdersMethod = new ListOrdersMethod($this->apiConfiguration);
        $listOrdersMethod->setQueryParameters($queryString);
        if ($asJson) {
            return $listOrdersMethod->call()->getResponseAsJson();
        }

        return $listOrdersMethod->call()->getOrders();
    }

    /**
     * @param      $orderId
     * @param bool $asJson
     *
     * @return bool|false|Order|string
     *
     * @throws \Exception
     */
    public function confirmOrder($orderId, $asJson = false)
    {
        $confirmOrderMethod = new ConfirmOrderMethod($this->apiConfiguration);
        $confirmOrderMethod->setOrderId($orderId);
        if ($asJson) {
            return $confirmOrderMethod->call()->getResponseAsJson();
        }

        return $confirmOrderMethod->call()->getOrder();
    }

    /**
     * @param              $orderId
     * @param Order\Refund $refund
     * @param bool         $asJson
     *
     * @return bool|false|Order\Refund|string
     *
     * @throws \Exception
     */
    public function refundOrder($orderId, Order\Refund $refund, $asJson = false)
    {
        $refundOrderMethod = new RefundOrderMethod($this->apiConfiguration);
        $refundOrderMethod->setOrderId($orderId);
        $refundOrderMethod->setRefund($refund);
        if ($asJson) {
            return $refundOrderMethod->call()->getResponseAsJson();
        }

        return $refundOrderMethod->call()->getRefund();
    }
}
