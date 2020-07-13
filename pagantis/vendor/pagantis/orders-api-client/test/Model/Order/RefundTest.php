<?php

namespace Test\Pagantis\OrdersApiClient\Model\Order;

use Faker\Factory;
use Pagantis\OrdersApiClient\Model\Order\Refund;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class RefundTest
 * @package Test\Pagantis\OrdersApiClient\Model\Order
 */
class RefundTest extends AbstractTest
{
    /**
     * testSetAmount
     */
    public function testSetAmount()
    {
        $faker = Factory::create();
        $number = $faker->randomDigitNotNull;
        $refund = new Refund();
        $refund->setTotalAmount($number);
        $this->assertEquals($number, $refund->getTotalAmount());
    }

    /**
     * testSetPromotedAmount
     */
    public function testSetPromotedAmount()
    {
        $faker = Factory::create();
        $number = $faker->randomDigitNotNull;
        $refund = new Refund();
        $refund->setPromotedAmount($number);
        $this->assertEquals($number, $refund->getPromotedAmount());
    }
}
