<?php

namespace Test\Pagantis\OrdersApiClient\Method;

use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class MethodInterfaceTest
 *
 * @package Test\Pagantis\OrdersApiClient\Method;
 */
class MethodInterfaceTest extends AbstractTest
{
    /**
     * testInterfaceExists
     */
    public function testInterfaceExists()
    {
        $interfaceMock = $this->getMock('Pagantis\OrdersApiClient\Method\MethodInterface');
        $this->assertInstanceOf('Pagantis\OrdersApiClient\Method\MethodInterface', $interfaceMock);
    }

    /**
     * testInterfaceHasMethodCall
     */
    public function testInterfaceHasMethodCall()
    {
        $interfaceMock = $this->getMock('Pagantis\OrdersApiClient\Method\MethodInterface');
        $this->assertTrue(method_exists($interfaceMock, 'call'));
    }
}
