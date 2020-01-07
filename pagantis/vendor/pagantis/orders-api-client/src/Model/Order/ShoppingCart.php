<?php

namespace Pagantis\OrdersApiClient\Model\Order;

use Pagantis\OrdersApiClient\Model\AbstractModel;
use Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details;

/**
 * Class ShoppingCart
 * @package Pagantis\OrdersApiClient\Model\Order
 */
class ShoppingCart extends AbstractModel
{
    /**
     * @var Details $details
     */
    protected $details;

    /**
     * @var string $order_reference Order reference in merchant side
     */
    protected $orderReference;

    /**
     * @var int $promotedAmount The part in cents from the totalAmount that is promoted
     */
    protected $promotedAmount;

    /**
     * @var int $totalAmount The total amount of the order in cents that will be charged to the user
     */
    protected $totalAmount;

    /**
     * Not adding setters nor getters
     *
     * @deprecated
     */
    protected $deprecatedOrderDescription;

    /**
     * ShoppingCart constructor.
     */
    public function __construct()
    {
        $this->details = new Details();
    }

    /**
     * @return Details
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param Details $details
     *
     * @return ShoppingCart
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderReference()
    {
        return $this->orderReference;
    }

    /**
     * @param string $orderReference
     *
     * @return ShoppingCart
     */
    public function setOrderReference($orderReference)
    {
        $this->orderReference = $orderReference;

        return $this;
    }

    /**
     * @return int
     */
    public function getPromotedAmount()
    {
        return $this->promotedAmount;
    }

    /**
     * @param $promotedAmount
     *
     * @return $this
     */
    public function setPromotedAmount($promotedAmount)
    {
        $this->promotedAmount = $promotedAmount;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param $totalAmount
     *
     * @return $this
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }
}
