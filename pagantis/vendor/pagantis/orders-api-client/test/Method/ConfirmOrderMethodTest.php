<?php

namespace Test\Pagantis\OrdersApiClient\Method;

use Faker\Factory;
use Httpful\Http;
use Httpful\Request;
use Pagantis\OrdersApiClient\Exception\ClientException;
use Pagantis\OrdersApiClient\Method\ConfirmOrderMethod;
use Pagantis\OrdersApiClient\Model\ApiConfiguration;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class ConfirmOrderMethodTest
 *
 * @package Test\Pagantis\OrdersApiClient\Method;
 */
class ConfirmOrderMethodTest extends AbstractTest
{
    /**
     * testEndpointConstant
     */
    public function testEndpointConstant()
    {
        $constant = ConfirmOrderMethod::ENDPOINT;
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
        $confirmOrderMethod = new ConfirmOrderMethod($apiConfigurationMock);
        $confirmOrderMethod->setOrderId($orderId);
        $reflectConfirmOrderMethod = new \ReflectionClass('Pagantis\OrdersApiClient\Method\ConfirmOrderMethod');
        $property = $reflectConfirmOrderMethod->getProperty('orderId');
        $property->setAccessible(true);
        $this->assertEquals($orderId, $property->getValue($confirmOrderMethod));
    }

    /**
     * testGetOrder
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testGetOrder()
    {
        $orderJson = file_get_contents($this->resourcePath.'Order.json');
        $responseMock = $this->getMockBuilder('Httpful\Response')->disableOriginalConstructor()->getMock();
        $responseMockReflect = new \ReflectionClass('Httpful\Response');
        $property = $responseMockReflect->getProperty('body');
        $property->setAccessible(true);
        $property->setValue($responseMock, json_decode($orderJson));

        $apiConfigurationMock = $this->getMock('Pagantis\OrdersApiClient\Model\ApiConfiguration');
        $confirmOrderMethod = new ConfirmOrderMethod($apiConfigurationMock);
        $this->assertFalse($confirmOrderMethod->getOrder());
        $reflectConfirmOrderMethod = new \ReflectionClass('Pagantis\OrdersApiClient\Method\ConfirmOrderMethod');
        $property = $reflectConfirmOrderMethod->getProperty('response');
        $property->setAccessible(true);
        $property->setValue($confirmOrderMethod, $responseMock);

        $this->assertInstanceOf('Pagantis\OrdersApiClient\Model\Order', $confirmOrderMethod->getOrder());
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
        $apiConfiguration = new ApiConfiguration();
        $apiConfiguration->setBaseUri($url);
        $confirmOrderMethod = new ConfirmOrderMethod($apiConfiguration);
        $reflectConfirmOrderMethod = new \ReflectionClass('Pagantis\OrdersApiClient\Method\ConfirmOrderMethod');
        $method = $reflectConfirmOrderMethod->getMethod('prepareRequest');
        $method->setAccessible(true);
        $property = $reflectConfirmOrderMethod->getProperty('request');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($confirmOrderMethod));
        $confirmOrderMethod->setOrderId($orderId);
        $method->invoke($confirmOrderMethod);
        /** @var Request $request */
        $request = $property->getValue($confirmOrderMethod);
        $this->assertInstanceOf('Httpful\Request', $request);
        $this->assertSame(Http::PUT, $request->method);
        $uri =
            $url .
            ConfirmOrderMethod::ENDPOINT .
            ConfirmOrderMethod::SLASH .
            $orderId .
            ConfirmOrderMethod::SLASH .
            ConfirmOrderMethod::CONFIRM_ENDPOINT
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
        $confirmOrderMethod = new ConfirmOrderMethod($apiConfigurationMock);
        try {
            $confirmOrderMethod->call();
            $this->assertTrue(false);
        } catch (ClientException $exception) {
            $this->assertInstanceOf('Pagantis\OrdersApiClient\Exception\ClientException', $exception);
        }
    }
}
