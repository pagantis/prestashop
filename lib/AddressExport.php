<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 */

class AddressExport
{
    /**
     * @param AddressCore $addressCore
     *
     * @return stdClass
     */
    public static function export(AddressCore $addressCore)
    {
        if (_PS_VERSION_ >= 1.7) {
            return (object) [
                'dni'         => $addressCore->dni,
                'firstName'   => $addressCore->firstname,
                'lastName'    => $addressCore->lastname,
                'fullName'    => $addressCore->firstname. ' ' .$addressCore->lastname,
                'mobilePhone' => $addressCore->phone_mobile,
                'phone'       => $addressCore->phone,
                'zipCode'     => $addressCore->postcode,
                'city'        => $addressCore->city,
                'street'      => $addressCore->address1.' '.$addressCore->address2,
                'address'     => json_decode(json_encode($addressCore)),
            ];
        }

        if (_PS_VERSION_ < 1.7) {
            return (object) [
                'dni'         => $addressCore->dni,
                'firstName'   => $addressCore->firstname,
                'lastName'    => $addressCore->lastname,
                'fullName'    => $addressCore->firstname. ' ' .$addressCore->lastname,
                'mobilePhone' => $addressCore->phone_mobile,
                'phone'       => $addressCore->phone,
                'zipCode'     => $addressCore->postcode,
                'city'        => $addressCore->city,
                'street'      => $addressCore->address1.' '.$addressCore->address2,
                'address'     => json_decode(json_encode($addressCore)),
            ];
        }
        return (object) [];
    }
}
