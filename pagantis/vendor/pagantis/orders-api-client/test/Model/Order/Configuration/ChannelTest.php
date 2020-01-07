<?php

namespace Test\Pagantis\OrdersApiClient\Model\Order\Configuration;

use Pagantis\OrdersApiClient\Model\Order\Configuration\Channel;
use Test\Pagantis\OrdersApiClient\AbstractTest;

/**
 * Class ChannelTest
 * @package Test\Pagantis\OrdersApiClient\Model\Order\Configuration
 */
class ChannelTest extends AbstractTest
{
    /**
     * Online type, for sales in the website
     */
    const ONLINE = 'ONLINE';

    /**
     * In store type, for sales in a physical store
     */
    const INSTORE = 'IN_STORE';

    /**
     * PhoneSale type, for sales made on the phone
     */
    const PHONESALE = 'PHONE';

    /**
     * testSetAssistedSale
     */
    public function testSetAssistedSale()
    {
        $channel = new Channel();
        $channel->setAssistedSale(null);
        $this->assertNull($channel->getAssistedSale());
        $channel->setAssistedSale(true);
        $this->assertTrue($channel->getAssistedSale());
    }

    /**
     * testSetType
     *
     * @throws \ReflectionException
     */
    public function testSetType()
    {
        $channel = new Channel();
        $reflectionClass = new \ReflectionClass(
            'Pagantis\OrdersApiClient\Model\Order\Configuration\Channel'
        );
        $constants = $reflectionClass->getConstants();
        foreach ($constants as $constant) {
            $channel->setType($constant);
            $this->assertEquals($constant, $channel->getType());
        }

        $channel->setType(null);
        $this->assertNull($channel->getType());
    }

    /**
     * testConstantsNotChange
     */
    public function testConstantsNotChange()
    {
        $this->assertSame(self::INSTORE, Channel::INSTORE);
        $this->assertSame(self::ONLINE, Channel::ONLINE);
        $this->assertSame(self::PHONESALE, Channel::PHONESALE);
    }
}
