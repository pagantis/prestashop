<?php

namespace Test\Pagantis\OrdersApiClient\Model;

use Pagantis\OrdersApiClient\Model\Order;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class AbstractModelTest
 *
 * @package Test\Pagantis\OrdersApiClient\Model
 */
class AbstractModelTest extends AbstractTest
{
    /**
     * complete testing, entire order validate, export and import
     *
     * @throws \Exception
     */
    public function testAllMethod()
    {
        $orderJson = file_get_contents($this->resourcePath.'Order.json');
        $object = json_decode($orderJson);
        $order = new Order();
        $order->import($object);
        $orderExport = json_decode(json_encode($order->export()));
        $orderExportJson = json_encode($order->export());

        foreach ($object as $key => $value) {
            if (null === $value) {
                unset($object->$key);
            }
        }

        $orderJson = json_encode($object);

        $this->assertEquals($object, $orderExport);
        $this->assertEquals($orderJson, $orderExportJson);
    }

    /**
     * testExportEmptyObjectReturnObject
     *
     * Test to confirm the object exported as json
     */
    public function testExportEmptyObjectReturnJSONObject()
    {
        $address = new Order\User\Address();
        $result = json_encode($address->export());
        $expectedResult = "{}";

        $this->assertSame($expectedResult, $result);
    }

    /**
     * testValidateDate
     *
     * @throws \ReflectionException
     */
    public function testValidateDate()
    {
        $abstractModelMock = $this->getMockBuilder('Pagantis\OrdersApiClient\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMock();
        $abstractModelReflection = new \ReflectionClass('Pagantis\OrdersApiClient\Model\AbstractModel');
        $method = $abstractModelReflection->getMethod('validateDate');
        $method->setAccessible(true);

        $correctValues = array(
            '2018-12-17T08:46:18.000+00:00',
            '2018-12-17T08:46:18.123+20:00',
            '2018-12-03T10:20:58+01:00',
            '2018-12-03T10:20:58',
            '2018-12-03T10:20:58.988+01:00',
        );

        $wrongValues = array(
            'APPLE IPHONE XS MAX 512GB GOLD SUPER RETINA HD/A12 BIONIC/LTE/DUAL 12MPX/4K/6.5 MT582QL/A',
            'A+NO FROST2 X 0,60 METROS NUEVO CON DOS AÑOS DE GARANTÍA .SOLO ENVÍOS EN LA COMUNIDAD DE MADRID ',
            '2018-12-03T10:20:58.A988+01:00',
            '0000-00-00T00:00:00',
            '0000-00-00T00:00:00.000',
            '2018-12-03',
        );

        foreach ($correctValues as $value) {
            $this->assertTrue($method->invokeArgs($abstractModelMock, array($value)), $value);
        }

        foreach ($wrongValues as $value) {
            $this->assertFalse($method->invokeArgs($abstractModelMock, array($value)), $value);
        }
    }
}
