<?php

namespace Test\Pagantis\OrdersApiClient\Model\Order\User;

use Faker\Factory;
use Pagantis\OrdersApiClient\Model\Order\User\OrderHistory;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class OrderHistoryTest
 * @package Test\Pagantis\OrdersApiClient\Model\Order\User
 */
class OrderHistoryTest extends AbstractTest
{
    /**
     * testSetAmount
     */
    public function testSetAmount()
    {
        $faker = Factory::create();
        $number = $faker->randomDigitNotNull;
        $orderHistory = new OrderHistory();
        $orderHistory->setAmount($number);
        $this->assertEquals($orderHistory->getAmount(), $number);
    }

    /**
     * testSetDateOfBirth
     */
    public function testSetDateOfBirth()
    {
        $beforeFiftyYears = date('Y-m-d', strtotime('-18 years'));
        $orderHistory = new OrderHistory();
        $this->assertNull($orderHistory->getDate());
        $orderHistory->setDate($beforeFiftyYears);
        $this->assertSame($beforeFiftyYears, $orderHistory->getDate());

        $nullDate = null;
        $orderHistory = new OrderHistory();
        $this->assertNull($orderHistory->getDate());
        $orderHistory->setDate($nullDate);
        $this->assertSame($nullDate, $orderHistory->getDate());

        $orderHistory = new OrderHistory();
        $this->assertNull($orderHistory->getDate());
        $orderHistory->setDate("31/7/2000 00:00:00");
        $this->assertNull($orderHistory->getDate());

        $orderHistory = new OrderHistory();
        $this->assertNull($orderHistory->getDate());
        $orderHistory->setDate("31/7/2000");
        $this->assertNull($orderHistory->getDate());

        $orderHistory = new OrderHistory();
        $this->assertNull($orderHistory->getDate());
        $orderHistory->setDate("null");
        $this->assertSame($orderHistory->getDate(), $nullDate);

        $orderHistory = new OrderHistory();
        $this->assertNull($orderHistory->getDate());
        $orderHistory->setDate("");
        $this->assertSame($orderHistory->getDate(), $nullDate);

        $orderHistory = new OrderHistory();
        $today = new \DateTime('today');
        $this->assertNull($orderHistory->getDate());
        $orderHistory->setDate($today);
        $this->assertSame($today->format('Y-m-d'), $orderHistory->getDate());

        $originalDate = '1985-05-25';
        $bornDate = date('Y-m-d H:i:s', strtotime($originalDate));
        $orderHistory = new OrderHistory();
        $this->assertNull($orderHistory->getDate());
        $orderHistory->setDate($bornDate);
        $this->assertSame($originalDate, $orderHistory->getDate());
    }
}
