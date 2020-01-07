<?php

namespace Test\Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details;

use Faker\Factory;
use Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details\Product;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class Product
 * @package Test\Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details
 */
class ProductTest extends AbstractTest
{
    /**
     * testSetAmount
     */
    public function testSetAmount()
    {
        $faker = Factory::create();
        $number = $faker->randomDigitNotNull;
        $product = new Product();
        $product->setQuantity($number);
        $this->assertEquals($product->getQuantity(), $number);
    }

    /**
     * testSetDescription
     */
    public function testSetDescription()
    {
        $faker = Factory::create();
        $sentence = $faker->sentence;
        $product = new Product();
        $product->setDescription($sentence);
        $this->assertEquals($product->getDescription(), $sentence);
        $product->setDescription(null);
        $this->assertEquals(null, $product->getDescription());
    }

    /**
     * testSetQuantity
     */
    public function testSetQuantity()
    {
        $faker = Factory::create();
        $number = $faker->randomDigitNotNull;
        $product = new Product();
        $product->setQuantity($number);
        $this->assertEquals($product->getQuantity(), $number);
    }
}
