<?php

namespace Test\Pagantis\OrdersApiClient\Model\Order\User;

use Faker\Factory;
use Pagantis\OrdersApiClient\Model\Order\User\Address;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class AddressTest
 * @package Test\Pagantis\OrdersApiClient\Model\Order\User
 */
class AddressTest extends AbstractTest
{
    /**
     * testSetFullName
     */
    public function testSetFullName()
    {
        $faker = Factory::create();
        $address = new Address();
        $fullName = $faker->name.' '.$faker->lastName;
        $address->setFullName($fullName);
        $this->assertSame($fullName, $address->getFullName());
    }

    /**
     * testSetTaxId
     */
    public function testSetTaxId()
    {
        $address = new Address();
        $taxId = 'A123456789B';
        $address->setTaxId($taxId);
        $this->assertSame($taxId, $address->getTaxId());
    }

    /**
     * testSetNationalId
     */
    public function testSetNationalId()
    {
        $address = new Address();
        $nationalId = 'A123456789B';
        $address->setNationalId($nationalId);
        $this->assertSame($nationalId, $address->getNationalId());
    }
}
