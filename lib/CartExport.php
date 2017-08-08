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
        if (_PS_VERSION_ >= 1.7) {
            return (object) array(
                'orderId' => $cartCore->id,
                'items' => $cartCore->getProducts(true),
                'amount' => intval(100 * $cartCore->getOrderTotal()),
                'shipping' => $cartCore->getTotalShippingCost(),
                'summary' => json_decode(json_encode($cartCore->getSummaryDetails()))
            );
        }

        if (_PS_VERSION_ < 1.7) {
            return (object) array(
                'orderId' => $cartCore->id,
                'items' => $cartCore->getProducts(true),
                'amount' => intval(100 * $cartCore->getOrderTotal()),
                'shipping' => $cartCore->getTotalShippingCost(),
                'summary' => json_decode(json_encode($cartCore->getSummaryDetails()))
            );
        }
        return (object) array();
    }
}
