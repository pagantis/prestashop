<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 */

class CartExport
{
    /**
     * @param CartCore $cartCore
     *
     * @return stdClass
     */
    public static function export(CartCore $cartCore)
    {
        $items = $cartCore->getProducts(true);
        foreach ($items as $key => $item) {
            $itemCategories = ProductCore::getProductCategoriesFull($item['id_product']);
            $items[$key]['categories'] = $itemCategories;
        }

        if (_PS_VERSION_ >= 1.7) {
            return (object) array(
                'orderId' => $cartCore->id,
                'items' => $cartCore->getProducts(true),
                'amount' => intval(strval(100 * $cartCore->getOrderTotal(true))),
                'shipping' => $cartCore->getTotalShippingCost(),
                'summary' => json_decode(json_encode($cartCore->getSummaryDetails()))
            );
        }

        if (_PS_VERSION_ < 1.7) {
            return (object) array(
                'orderId' => $cartCore->id,
                'items' => $cartCore->getProducts(true),
                'amount' => intval(strval(100 * $cartCore->getOrderTotal(true))),
                'shipping' => $cartCore->getTotalShippingCost(),
                'summary' => json_decode(json_encode($cartCore->getSummaryDetails()))
            );
        }
        return (object) array();
    }
}
