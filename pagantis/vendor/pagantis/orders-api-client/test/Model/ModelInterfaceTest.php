<?php

namespace Test\Pagantis\OrdersApiClient\Model;

use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class ModelInterfaceTest
 *
 * @package Test\Pagantis\OrdersApiClient\Model
 */
class ModelInterfaceTest extends AbstractTest
{
    /**
     * testInterfaceExists
     */
    public function testInterfaceExists()
    {
        $interfaceMock = $this->getMock('Pagantis\OrdersApiClient\Model\ModelInterface');
        $this->assertInstanceOf('Pagantis\OrdersApiClient\Model\ModelInterface', $interfaceMock);
    }

    /**
     * testInterfaceExists
     */
    public function testInterfaceHasMethodExport()
    {
        $interfaceMock = $this->getMock('Pagantis\OrdersApiClient\Model\ModelInterface');
        $this->assertTrue(method_exists($interfaceMock, 'export'));
    }

    /**
     * testInterfaceExists
     */
    public function testInterfaceHasMethodImport()
    {
        $interfaceMock = $this->getMock('Pagantis\OrdersApiClient\Model\ModelInterface');
        $this->assertTrue(method_exists($interfaceMock, 'import'));
    }
}
