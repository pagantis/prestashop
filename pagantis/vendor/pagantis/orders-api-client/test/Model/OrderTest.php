<?php

namespace Test\Pagantis\OrdersApiClient\Model;

use Pagantis\OrdersApiClient\Model\Order;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class OrderTest
 *
 * @package Test\Pagantis\OrdersApiClient\Model
 */
class OrderTest extends AbstractTest
{
    /**
     * Initial status of a order.
     */
    const STATUS_CREATED = 'CREATED';

    /**
     * Order has been authorized and initial payment has been approved. For finalizing the order
     * it's mandatory to confirm it.
     */
    const STATUS_AUTHORIZED = 'AUTHORIZED';

    /**
     * Order confirmed has been paid by customer and merchant has confirmed it. Payment is completed
     * and settlement will be created.
     */
    const STATUS_CONFIRMED = 'CONFIRMED';

    /**
     * Rejected by the risk engine, the transaction has been rejected and payment is no longer
     * expected nor possible.
     */
    const STATUS_REJECTED = 'REJECTED';

    /**
     * The order has been invalidated due to the expiration limit. If no action happens during the
     * defined time, the order could turn to invalidated.
     */
    const STATUS_INVALIDATED = 'INVALIDATED';

    /**
     * Undefined ERROR has occurred, please double check with the account manager or Pagantis support channels.
     */
    const STATUS_ERROR = 'ERROR';

    /**
     * If a order is not confirmed given the default confirmation time, defined previously, it will turn to
     * unconfirmed and this will refund any possible payment taken from the customer. The loan shall not be created.
     */
    const STATUS_UNCONFIRMED = 'UNCONFIRMED';

    /**
     * testConstructor
     */
    public function testConstructor()
    {
        $order = new Order();
        $this->assertInstanceOf(
            'Pagantis\OrdersApiClient\Model\Order\User',
            $order->getUser()
        );
        $this->assertNull(
            $order->getActionUrls()
        );
        $this->assertInstanceOf(
            'Pagantis\OrdersApiClient\Model\Order\Configuration',
            $order->getConfiguration()
        );
        $this->assertInstanceOf(
            'Pagantis\OrdersApiClient\Model\Order\ShoppingCart',
            $order->getShoppingCart()
        );
        $this->assertInstanceOf(
            'Pagantis\OrdersApiClient\Model\Order\Metadata',
            $order->getMetadata()
        );

        $this->assertNull($order->getConfirmedAt());
        $this->assertNull($order->getCreatedAt());
        $this->assertNull($order->getExpiresAt());
        $this->assertNull($order->getRefunds());
    }

    /**
     * testImport
     * @throws \Exception
     */
    public function testImport()
    {
        $orderJson = file_get_contents($this->resourcePath.'Order.json');
        $object = json_decode($orderJson);

        foreach ($object as $key => $value) {
            if (null === $value) {
                unset($object->$key);
            }
        }

        $order = new Order();
        $order->import($object);
        $orderExport = json_decode(json_encode($order->export()));
        $this->assertEquals($object, $orderExport);
    }

    /**
     * testImport
     * @throws \Exception
     */
    public function testImportEmptyDates()
    {
        $orderJson = file_get_contents($this->resourcePath.'Order.json');
        $object = json_decode($orderJson);

        foreach ($object as $key => $value) {
            if (null === $value) {
                unset($object->$key);
            }
        }

        $order = new Order();
        $order->import($object);
        $orderExport = json_decode(json_encode($order->export()));
        $this->assertEquals($object, $orderExport);
    }

    /**
     * testConstantsNotChange
     */
    public function testConstantsNotChange()
    {
        $this->assertEquals(self::STATUS_AUTHORIZED, Order::STATUS_AUTHORIZED);
        $this->assertEquals(self::STATUS_CONFIRMED, Order::STATUS_CONFIRMED);
        $this->assertEquals(self::STATUS_CREATED, Order::STATUS_CREATED);
        $this->assertEquals(self::STATUS_REJECTED, Order::STATUS_REJECTED);
        $this->assertEquals(self::STATUS_INVALIDATED, Order::STATUS_INVALIDATED);
        $this->assertEquals(self::STATUS_ERROR, Order::STATUS_ERROR);
        $this->assertEquals(self::STATUS_UNCONFIRMED, Order::STATUS_UNCONFIRMED);
    }
}
