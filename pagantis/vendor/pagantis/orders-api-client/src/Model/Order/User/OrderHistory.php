<?php

namespace Pagantis\OrdersApiClient\Model\Order\User;

use Pagantis\OrdersApiClient\Model\AbstractModel;

/**
 * Class OrderHistory
 * @package Pagantis\OrdersApiClient\Model\Order\User
 */
class OrderHistory extends AbstractModel
{
    /**
     * @var int $amount
     */
    protected $amount;

    /**
     * @var string $date
     */
    protected $date;

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     *
     * @return OrderHistory
     */
    public function setDate($date)
    {
        $this->date = $this->checkDateFormat($date);

        return $this;
    }
}
