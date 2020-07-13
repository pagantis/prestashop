<?php

namespace Test\Pagantis\OrdersApiClient\Method;

use Faker\Factory;
use Httpful\Http;
use Httpful\Request;
use Pagantis\OrdersApiClient\Exception\ClientException;
use Pagantis\OrdersApiClient\Method\RefundOrderMethod;
use Pagantis\OrdersApiClient\Model\ApiConfiguration;
use Pagantis\OrdersApiClient\Model\Order\Refund;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class RefundOrderMethodTest
 *
 * @package Test\Pagantis\OrdersApiClient\Method;
 */
class RefundOrderMethodTest extends AbstractTest
{
    /**
     * testEndpointConstant
     */
    public function testEndpointConstant()
    {
        $constant = RefundOrderMethod::ENDPOINT;
        $this->assertEquals('/orders', $constant);
    }

    /**
     * testSetOrderId
     *
     * @throws \ReflectionException
     */
    public function testSetOrderId()
    {
        $faker = Factory::create();
        $orderId = $faker->uuid;
        $apiConfigurationMock = $this->getMock('Pagantis\OrdersApiClient\Model\ApiConfiguration');
        $refundOrderMethod = new RefundOrderMethod($apiConfigurationMock);
        $refundOrderMethod->setOrderId($orderId);
        $reflectRefundOrderMethod = new \ReflectionClass('Pagantis\OrdersApiClient\Method\RefundOrderMethod');
        $property = $reflectRefundOrderMethod->getProperty('orderId');
        $property->setAccessible(true);
        $this->assertEquals($orderId, $property->getValue($refundOrderMethod));
    }

    /**
     * testSetRefund
     *
     * @throws \ReflectionException
     */
    public function testSetRefund()
    {
        $refund = new Refund();
        $apiConfigurationMock = $this->getMock('Pagantis\OrdersApiClient\Model\ApiConfiguration');
        $refundOrderMethod = new RefundOrderMethod($apiConfigurationMock);
        $refundOrderMethod->setRefund($refund);
        $reflectRefundOrderMethod = new \ReflectionClass('Pagantis\OrdersApiClient\Method\RefundOrderMethod');
        $property = $reflectRefundOrderMethod->getProperty('refund');
        $property->setAccessible(true);
        $this->assertSame($refund, $property->getValue($refundOrderMethod));
    }

    /**
     * testGetRefund
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testGetRefund()
    {
        $orderJson = file_get_contents('test/Resources/Refund.json');
        $responseMock = $this->getMockBuilder('Httpful\Response')->disableOriginalConstructor()->getMock();
        $responseMockReflect = new \ReflectionClass('Httpful\Response');
        $property = $responseMockReflect->getProperty('body');
        $property->setAccessible(true);
        $property->setValue($responseMock, json_decode($orderJson));

        $apiConfigurationMock = $this->getMock('Pagantis\OrdersApiClient\Model\ApiConfiguration');
        $refundOrderMethod = new RefundOrderMethod($apiConfigurationMock);
        $this->assertFalse($refundOrderMethod->getRefund());
        $reflectRefundOrderMethod = new \ReflectionClass('Pagantis\OrdersApiClient\Method\RefundOrderMethod');
        $property = $reflectRefundOrderMethod->getProperty('response');
        $property->setAccessible(true);
        $property->setValue($refundOrderMethod, $responseMock);

        $this->assertInstanceOf('Pagantis\OrdersApiClient\Model\Order\Refund', $refundOrderMethod->getRefund());
    }

    /**
     * testPrepareRequest
     *
     * @throws \ReflectionException
     * @throws ClientException
     */
    public function testPrepareRequest()
    {
        $faker = Factory::create();
        $url = $faker->url;
        $orderId = $faker->uuid;
        $refund = new Refund();
        $refund
            ->setTotalAmount($faker->randomDigitNotNull)
        ;
        $apiConfiguration = new ApiConfiguration();
        $apiConfiguration->setBaseUri($url);
        $refundOrderMethod = new RefundOrderMethod($apiConfiguration);
        $refundOrderMethod->setRefund($refund);
        $reflectRefundOrderMethod = new \ReflectionClass('Pagantis\OrdersApiClient\Method\RefundOrderMethod');
        $method = $reflectRefundOrderMethod->getMethod('prepareRequest');
        $method->setAccessible(true);
        $property = $reflectRefundOrderMethod->getProperty('request');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($refundOrderMethod));
        $refundOrderMethod->setOrderId($orderId);
        $method->invoke($refundOrderMethod);
        /** @var Request $request */
        $request = $property->getValue($refundOrderMethod);
        $this->assertInstanceOf('Httpful\Request', $request);
        $this->assertSame(Http::POST, $request->method);
        $uri =
            $url .
            RefundOrderMethod::ENDPOINT .
            RefundOrderMethod::SLASH .
            $orderId .
            RefundOrderMethod::SLASH .
            RefundOrderMethod::REFUND_ENDPOINT
        ;
        $this->assertSame($uri, $request->uri);
    }

    /**
     * testCall
     *
     * @throws \Httpful\Exception\ConnectionErrorException
     * @throws \Pagantis\OrdersApiClient\Exception\HttpException
     */
    public function testCall()
    {
        $apiConfigurationMock = $this->getMock('Pagantis\OrdersApiClient\Model\ApiConfiguration');
        $refundOrderMethod = new RefundOrderMethod($apiConfigurationMock);
        try {
            $refundOrderMethod->call();
            $this->assertTrue(false);
        } catch (ClientException $exception) {
            $this->assertInstanceOf('Pagantis\OrdersApiClient\Exception\ClientException', $exception);
        }
    }
}
