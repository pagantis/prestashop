<?php

namespace Pagantis\OrdersApiClient\Model\Order\ShoppingCart;

use Pagantis\OrdersApiClient\Model\AbstractModel;
use Pagantis\OrdersApiClient\Model\Order\ShoppingCart\Details\Product;

/**
 * Class Details
 * @package Pagantis\OrdersApiClient\Model\Order\ShoppingCart
 */
class Details extends AbstractModel
{
    /**
     * @var Product[]
     */
    protected $products;

    /**
     * @var int $shipping_cost Shipping cost for the order
     */
    protected $shippingCost;

    /**
     * Details constructor.
     */
    public function __construct()
    {
        $this->products = array();
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param Product $product
     *
     * @return Details
     */
    public function addProduct(Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * @return int
     */
    public function getShippingCost()
    {
        return $this->shippingCost;
    }

    /**
     * @param $shippingCost
     *
     * @return $this
     */
    public function setShippingCost($shippingCost)
    {
        $this->shippingCost = $shippingCost;

        return $this;
    }

    /**
     * Overwrite import to fill products correctly
     *
     * @param \stdClass $object
     *
     * @throws \Exception
     */
    public function import($object)
    {
        parent::import($object);
        $properties = get_object_vars($object);
        foreach ($properties as $key => $value) {
            if (is_array($value) && is_array($this->{$key}) && $key == 'products') {
                $this->products = array();
                foreach ($value as $product) {
                    $productObject = new Product();
                    $productObject->import($product);
                    $this->addProduct($productObject);
                }
            }
        }
    }
}
