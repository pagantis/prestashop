<?php

namespace Test\Pagantis\OrdersApiClient\Method;

use Faker\Factory;
use Httpful\Http;
use Httpful\Request;
use Pagantis\OrdersApiClient\Exception\ClientException;
use Pagantis\OrdersApiClient\Method\CreateOrderMethod;
use Pagantis\OrdersApiClient\Model\ApiConfiguration;
use Pagantis\OrdersApiClient\Model\Order;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class CreateOrderMethodTest
 *
 * @package Test\Pagantis\OrdersApiClient\Method;
 */
class CreateOrderMethodTest extends AbstractTest
{
    /**
     * testEndpointConstant
     */
    public function testEndpointConstant()
    {
        $constant = CreateOrderMethod::ENDPOINT;
        $this->assertEquals('/orders', $constant);
    }

    /**
     * testSetOrderId
     *
     * @throws \ReflectionException
     */
    public function testSetOrderId()
    {
        $order = new Order();
        $apiConfigurationMock = $this->getMock('Pagantis\OrdersApiClient\Model\ApiConfiguration');
        $createOrderMethod = new CreateOrderMethod($apiConfigurationMock);
        $createOrderMethod->setOrder($order);
        $reflectCreateOrderMethod = new \ReflectionClass('Pagantis\OrdersApiClient\Method\CreateOrderMethod');
        $property = $reflectCreateOrderMethod->getProperty('order');
        $property->setAccessible(true);
        $this->assertSame($order, $property->getValue($createOrderMethod));
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
        $createOrderMethod = new CreateOrderMethod($apiConfigurationMock);
        $this->assertFalse($createOrderMethod->getOrder());
        $reflectCreateOrderMethod = new \ReflectionClass('Pagantis\OrdersApiClient\Method\CreateOrderMethod');
        $property = $reflectCreateOrderMethod->getProperty('response');
        $property->setAccessible(true);
        $property->setValue($createOrderMethod, $responseMock);

        $this->assertInstanceOf('Pagantis\OrdersApiClient\Model\Order', $createOrderMethod->getOrder());
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
        $order = $this->getMock('Pagantis\OrdersApiClient\Model\Order');
        $apiConfiguration = new ApiConfiguration();
        $apiConfiguration->setBaseUri($url);
        $createOrderMethod = new CreateOrderMethod($apiConfiguration);
        $reflectCreateOrderMethod = new \ReflectionClass('Pagantis\OrdersApiClient\Method\CreateOrderMethod');
        $method = $reflectCreateOrderMethod->getMethod('prepareRequest');
        $method->setAccessible(true);
        $property = $reflectCreateOrderMethod->getProperty('request');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($createOrderMethod));
        $createOrderMethod->setOrder($order);
        $method->invoke($createOrderMethod);
        /** @var Request $request */
        $request = $property->getValue($createOrderMethod);
        $this->assertInstanceOf('Httpful\Request', $request);
        $this->assertSame(Http::POST, $request->method);
        $uri =
            $url .
            CreateOrderMethod::ENDPOINT
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
        $createOrderMethod = new CreateOrderMethod($apiConfigurationMock);
        try {
            $createOrderMethod->call();
            $this->assertTrue(false);
        } catch (ClientException $exception) {
            $this->assertInstanceOf('Pagantis\OrdersApiClient\Exception\ClientException', $exception);
        }
    }
}
