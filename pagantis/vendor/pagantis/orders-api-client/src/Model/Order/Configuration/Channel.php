<?php

namespace Pagantis\OrdersApiClient\Model\Order\Configuration;

use Pagantis\OrdersApiClient\Model\AbstractModel;

/**
 * Class Channel
 * @package Pagantis\OrdersApiClient\Model\Order\Configuration
 */
class Channel extends AbstractModel
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
     * @var string type
     */
    protected $type;

    /**
     * @var bool $assistedSale
     */
    protected $assistedSale;

    /**
     * @return bool
     */
    public function getAssistedSale()
    {
        return $this->assistedSale;
    }

    /**
     * @param $assistedSale
     *
     * @return $this

     */
    public function setAssistedSale($assistedSale)
    {
        $this->assistedSale = $assistedSale;

        return $this;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
