<?php

namespace Test\Pagantis\OrdersApiClient\Model\Order;

use Faker\Factory;
use Pagantis\OrdersApiClient\Model\Order\Metadata;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class MetadataTest
 * @package Test\Pagantis\OrdersApiClient\Model\Order
 */
class MetadataTest extends AbstractTest
{
    /**
     * testAddMetadata
     */
    public function testAddMetadata()
    {
        $faker = Factory::create();
        $metadata = new Metadata();
        $key = $faker->randomLetter;
        $value = $faker->sentence;
        $metadata->addMetadata($key, $value);
        $metadataExport = $metadata->export();
        $this->assertSame($value, $metadataExport->{$key});
    }

    /**
     * testImport
     */
    public function testImport()
    {
        $metadata = new Metadata();

        $orderJson = file_get_contents($this->resourcePath.'Order.json');
        $object = json_decode($orderJson);
        $object = $object->metadata;

        $metadata->import($object);
        $this->assertEquals($object, json_decode(json_encode($metadata->export())));
    }
}
