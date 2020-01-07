<?php

namespace Test\Pagantis\OrdersApiClient\Method;

use Httpful\Request;
use Pagantis\OrdersApiClient\Exception\HttpException;
use Pagantis\OrdersApiClient\Method\AbstractMethod;
use Pagantis\OrdersApiClient\Model\ApiConfiguration;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class AbstractMethodTest
 *
 * @package Test\Pagantis\OrdersApiClient\Method;
 */
class AbstractMethodTest extends AbstractTest
{
    /**
     * Has Slash
     */
    public function testHasSlashConstant()
    {
        $constant = AbstractMethod::SLASH;
        $this->assertEquals('/', $constant);
    }

    /**
     * Test Constructor
     *
     * @throws \ReflectionException
     */
    public function testConstructor()
    {
        $apiConfiguration = new ApiConfiguration();
        $abstractMethod = $this->getMock(
            'Pagantis\OrdersApiClient\Method\AbstractMethod',
            array('call'),
            array($apiConfiguration)
        );

        $reflectedClass = new \ReflectionClass('Pagantis\OrdersApiClient\Method\AbstractMethod');
        $property = $reflectedClass->getProperty('apiConfiguration');
        $property->setAccessible(true);
        $this->assertSame($apiConfiguration, $property->getValue($abstractMethod));
    }

    /**
     * Test get Request
     *
     * @throws \ReflectionException
     */
    public function testGetRequest()
    {
        $headers = array('key' => 'value');
        $publicKey = 'publicKey';
        $privateKey = 'privateKey';

        $apiConfiguration = new ApiConfiguration();
        $apiConfiguration
            ->setHeaders($headers)
            ->setPrivateKey($privateKey)
            ->setPublicKey($publicKey)
        ;
        $abstractMethod = $this->getMock(
            'Pagantis\OrdersApiClient\Method\AbstractMethod',
            array('call'),
            array($apiConfiguration)
        );

        $reflectedClass = new \ReflectionClass('Pagantis\OrdersApiClient\Method\AbstractMethod');
        $method = $reflectedClass->getMethod('getRequest');
        $method->setAccessible(true);
        /** @var Request $request */
        $request = $method->invoke($abstractMethod);
        $this->assertInstanceOf('Httpful\Request', $request);

        $this->assertSame($headers, $request->headers);
        $this->assertSame($publicKey, $request->username);
        $this->assertSame($privateKey, $request->password);
    }

    /**
     * testGetResponse
     *
     * @throws \ReflectionException
     */
    public function testGetResponse()
    {
        $apiConfiguration = new ApiConfiguration();
        $abstractMethod = $this->getMock(
            'Pagantis\OrdersApiClient\Method\AbstractMethod',
            array('call'),
            array($apiConfiguration)
        );

        $reflectedClass = new \ReflectionClass('Pagantis\OrdersApiClient\Method\AbstractMethod');
        $method = $reflectedClass->getMethod('getResponse');
        $method->setAccessible(true);
        $this->assertFalse($method->invoke($abstractMethod));

        $responseMock = $this->getMockBuilder('Httpful\Response')->disableOriginalConstructor()->getMock();
        $property = $reflectedClass->getProperty('response');
        $property->setAccessible(true);
        $property->setValue($abstractMethod, $responseMock);
        $this->assertInstanceOf('Httpful\Response', $method->invoke($abstractMethod));
    }

    /**
     * testGetResponseAsJson
     *
     * @throws \ReflectionException
     */
    public function testGetResponseAsJson()
    {
        $apiConfiguration = new ApiConfiguration();
        $abstractMethod = $this->getMock(
            'Pagantis\OrdersApiClient\Method\AbstractMethod',
            array('call'),
            array($apiConfiguration)
        );

        $reflectedClass = new \ReflectionClass('Pagantis\OrdersApiClient\Method\AbstractMethod');
        $method = $reflectedClass->getMethod('getResponseAsJson');
        $method->setAccessible(true);
        $this->assertFalse($method->invoke($abstractMethod));

        $json = 'body';
        $responseMock = $this->getMockBuilder('Httpful\Response')->disableOriginalConstructor()->getMock();
        $responseMockReflect = new \ReflectionClass('Httpful\Response');
        $property = $responseMockReflect->getProperty('raw_body');
        $property->setAccessible(true);
        $property->setValue($responseMock, $json);

        $property = $reflectedClass->getProperty('response');
        $property->setAccessible(true);
        $property->setValue($abstractMethod, $responseMock);
        $this->assertSame($json, $method->invoke($abstractMethod));
    }

    /**
     * Test Add get parameters work correctly
     *
     * @throws \ReflectionException
     */
    public function testAddGetParameters()
    {
        $apiConfiguration = new ApiConfiguration();
        $abstractMethod = $this->getMock(
            'Pagantis\OrdersApiClient\Method\AbstractMethod',
            array('call'),
            array($apiConfiguration)
        );

        $reflectedClass = new \ReflectionClass('Pagantis\OrdersApiClient\Method\AbstractMethod');
        $method = $reflectedClass->getMethod('addGetParameters');
        $method->setAccessible(true);
        $this->assertEquals('', $method->invoke($abstractMethod, array()));
        $this->assertEquals('?id=123', $method->invoke($abstractMethod, array('id' => 123)));
    }

    /**
     * Test Parse HTTP Exceptions
     *
     * @throws \ReflectionException
     */
    public function testParseHttpException()
    {
        $apiConfiguration = new ApiConfiguration();
        $abstractMethod = $this->getMock(
            'Pagantis\OrdersApiClient\Method\AbstractMethod',
            array('call'),
            array($apiConfiguration)
        );

        $reflectedClass = new \ReflectionClass('Pagantis\OrdersApiClient\Method\AbstractMethod');
        $method = $reflectedClass->getMethod('parseHttpException');
        $method->setAccessible(true);
        try {
            $method->invoke($abstractMethod, HttpException::HTTP_BAD_REQUEST);
        } catch (\Exception $exception) {
            $this->assertInstanceOf('Pagantis\OrdersApiClient\Exception\HttpException', $exception);
        }
        try {
            $method->invoke($abstractMethod, HttpException::HTTP_UNAUTHORIZED);
        } catch (\Exception $exception) {
            $this->assertInstanceOf('Pagantis\OrdersApiClient\Exception\HttpException', $exception);
        }
        try {
            $method->invoke($abstractMethod, HttpException::HTTP_FORBIDDEN);
        } catch (\Exception $exception) {
            $this->assertInstanceOf('Pagantis\OrdersApiClient\Exception\HttpException', $exception);
        }
        try {
            $method->invoke($abstractMethod, HttpException::HTTP_NOT_FOUND);
        } catch (\Exception $exception) {
            $this->assertInstanceOf('Pagantis\OrdersApiClient\Exception\HttpException', $exception);
        }
        try {
            $method->invoke($abstractMethod, HttpException::HTTP_METHOD_NOT_ALLOWED);
        } catch (\Exception $exception) {
            $this->assertInstanceOf('Pagantis\OrdersApiClient\Exception\HttpException', $exception);
        }
        try {
            $method->invoke($abstractMethod, HttpException::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $exception) {
            $this->assertInstanceOf('Pagantis\OrdersApiClient\Exception\HttpException', $exception);
        }
        try {
            $method->invoke($abstractMethod, HttpException::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $exception) {
            $this->assertInstanceOf('Pagantis\OrdersApiClient\Exception\HttpException', $exception);
        }
        try {
            $method->invoke($abstractMethod, HttpException::HTTP_SERVICE_UNAVAILABLE);
        } catch (\Exception $exception) {
            $this->assertInstanceOf('Pagantis\OrdersApiClient\Exception\HttpException', $exception);
        }
    }

    /**
     * testSetResponse
     *
     * @expectedException Pagantis\OrdersApiClient\Exception\HttpException
     *
     * @throws \ReflectionException
     */
    public function testSetResponseException()
    {
        $responseMock = $this
            ->getMockBuilder('Httpful\Response')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $abstractMethod = $this
            ->getMockBuilder('Pagantis\OrdersApiClient\Method\AbstractMethod')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $reflectedClass = new \ReflectionClass('Pagantis\OrdersApiClient\Method\AbstractMethod');
        $method = $reflectedClass->getMethod('setResponse');
        $method->setAccessible(true);

        $responseMock->code = HttpException::HTTP_INTERNAL_SERVER_ERROR;
        $responseMock->method('hasErrors')->willReturn(true);
        $this->assertInstanceOf(
            'Pagantis\OrdersApiClient\Method\AbstractMethod',
            $method->invoke($abstractMethod, $responseMock)
        );
    }

    /**
     * testSetResponse
     *
     * @throws \ReflectionException
     */
    public function testSetResponse()
    {
        $responseMock = $this
            ->getMockBuilder('Httpful\Response')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $abstractMethod = $this
            ->getMockBuilder('Pagantis\OrdersApiClient\Method\AbstractMethod')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $reflectedClass = new \ReflectionClass('Pagantis\OrdersApiClient\Method\AbstractMethod');
        $method = $reflectedClass->getMethod('setResponse');
        $method->setAccessible(true);

        $responseMock->method('hasErrors')->willReturn(false);
        $this->assertInstanceOf(
            'Pagantis\OrdersApiClient\Method\AbstractMethod',
            $method->invoke($abstractMethod, $responseMock)
        );
    }
}
