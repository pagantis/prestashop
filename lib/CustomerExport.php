<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 */

class CustomerExport
{
    /**
     * @param CustomerCore $customerCore
     *
     * @return stdClass
     */
    public static function export(CustomerCore $customerCore)
    {
        if (_PS_VERSION_ >= 1.7) {
            return (object) array(
                'id' => $customerCore->id,
                'gender' => $customerCore->id_gender,
                'email' => $customerCore->email,
                'dob' => $customerCore->birthday,
                'firstName' => $customerCore->firstname,
                'lastName' => $customerCore->lastname,
                'fullName'    => $customerCore->firstname. ' ' .$customerCore->lastname,
                'isGuest' => $customerCore->is_guest,
                'memberSince' => $customerCore->date_add,
                'customer' => json_decode(json_encode($customerCore)),
                'orders' => Order::getCustomerOrders($customerCore->id),
            );
        }

        if (_PS_VERSION_ < 1.7) {
            return (object) array(
                'id' => $customerCore->id,
                'gender' => $customerCore->id_gender,
                'email' => $customerCore->email,
                'dob' => $customerCore->birthday,
                'firstName' => $customerCore->firstname,
                'lastName' => $customerCore->lastname,
                'fullName'    => $customerCore->firstname. ' ' .$customerCore->lastname,
                'isGuest' => $customerCore->is_guest,
                'memberSince' => $customerCore->date_add,
                'customer' => json_decode(json_encode($customerCore)),
                'orders' => Order::getCustomerOrders($customerCore->id),
            );
        }
        return (object) array();
    }
}
