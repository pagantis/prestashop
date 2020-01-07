<?php

namespace Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details;

use Pagantis\OrdersApiClient\Model\AbstractModel;

/**
 * Class Product
 * @package Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details
 */
class Product extends AbstractModel
{
    /**
     * @var int $amount amount in cents of a product
     */
    protected $amount;

    /**
     * @var string $description the description of the product, normally name is enough
     */
    protected $description;

    /**
     * @var int $quantity number of items of this type
     */
    protected $quantity;

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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param $quantity
     *
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }
}
