<?php

namespace Test\Pagantis\OrdersApiClient\Model\Order;

use Faker\Factory;
use Pagantis\OrdersApiClient\Model\Order\User;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class UserTest
 * @package Test\Pagantis\OrdersApiClient\Model\Order
 */
class UserTest extends AbstractTest
{
    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $user = new User();
        $this->assertInstanceOf(
            'Pagantis\OrdersApiClient\Model\Order\User\Address',
            $user->getAddress()
        );
        $this->assertInstanceOf(
            'Pagantis\OrdersApiClient\Model\Order\User\Address',
            $user->getBillingAddress()
        );
        $this->assertInstanceOf(
            'Pagantis\OrdersApiClient\Model\Order\User\Address',
            $user->getShippingAddress()
        );
        $this->assertTrue(is_array($user->getOrderHistory()));
    }

    /**
     * testSetDateOfBirth
     */
    public function testSetDateOfBirth()
    {
        $beforeFiftyYears = date('Y-m-d', strtotime('-18 years'));
        $user = new User();
        $this->assertNull($user->getDateOfBirth());
        $user->setDateOfBirth($beforeFiftyYears);
        $this->assertSame($beforeFiftyYears, $user->getDateOfBirth());

        $nullDate = null;
        $user = new User();
        $this->assertNull($user->getDateOfBirth());
        $user->setDateOfBirth($nullDate);
        $this->assertSame($nullDate, $user->getDateOfBirth());

        $user = new User();
        $this->assertNull($user->getDateOfBirth());
        $user->setDateOfBirth("31/7/2000 00:00:00");
        $this->assertNull($user->getDateOfBirth());

        $user = new User();
        $this->assertNull($user->getDateOfBirth());
        $user->setDateOfBirth("31/7/2000");
        $this->assertNull($user->getDateOfBirth());

        $user = new User();
        $this->assertNull($user->getDateOfBirth());
        $user->setDateOfBirth("null");
        $this->assertSame($user->getDateOfBirth(), $nullDate);

        $user = new User();
        $this->assertNull($user->getDateOfBirth());
        $user->setDateOfBirth("");
        $this->assertSame($user->getDateOfBirth(), $nullDate);

        $user = new User();
        $today = new \DateTime('today');
        $this->assertNull($user->getDateOfBirth());
        $user->setDateOfBirth($today);
        $this->assertSame($today->format('Y-m-d'), $user->getDateOfBirth());

        $originalDate = '1985-05-25';
        $bornDate = date('Y-m-d H:i:s', strtotime($originalDate));
        $user = new User();
        $this->assertNull($user->getDateOfBirth());
        $user->setDateOfBirth($bornDate);
        $this->assertSame($originalDate, $user->getDateOfBirth());
    }

    /**
     * Test SetEmail
     */
    public function testSetEmail()
    {
        $faker = Factory::create();
        $user = new User();
        $email = $faker->email;
        $user->setEmail($email);
        $this->assertSame($email, $user->getEmail());
    }

    /**
     * testSetFullName
     */
    public function testSetFullName()
    {
        $faker = Factory::create();
        $user = new User();
        $fullName = $faker->name . ' ' . $faker->lastName;
        $user->setFullName($fullName);
        $this->assertSame($fullName, $user->getFullName());
    }

    /**
     * testSetTaxId
     */
    public function testSetTaxId()
    {
        $user = new User();
        $taxId = 'A123456789B';
        $user->setTaxId($taxId);
        $this->assertSame($taxId, $user->getTaxId());
    }

    /**
     * testSetNationalId
     */
    public function testSetNationalId()
    {
        $user = new User();
        $nationalId = 'A123456789B';
        $user->setNationalId($nationalId);
        $this->assertSame($nationalId, $user->getNationalId());
    }

    /**
     * testAddOrderHistory
     */
    public function testAddOrderHistory()
    {
        $user = new User();
        $orderHistoryMock = $this->getMock(
            'Pagantis\OrdersApiClient\Model\Order\User\OrderHistory'
        );
        $user->addOrderHistory($orderHistoryMock);
        $ordersHistory = $user->getOrderHistory();
        $objectOrderHistory = array_pop($ordersHistory);
        $this->assertSame($orderHistoryMock, $objectOrderHistory);
    }

    /**
     * testImport
     * @throws \Exception
     */
    public function testImport()
    {
        $orderJson = file_get_contents($this->resourcePath.'Order.json');
        $object = json_decode($orderJson);
        $object = $object->user;
        $user = new User();
        $user->import($object);
        $this->assertEquals($object, json_decode(json_encode($user->export())));
    }
}
