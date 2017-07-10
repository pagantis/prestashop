<?php

class AddressExport
{
    /**
     * @param AddressCore $addressCore
     *
     * @return array
     */
    public static function export(AddressCore $addressCore)
    {
        if (_PS_VERSION_ >= 1.7) {
            return [
                'dni'         => $addressCore->dni,
                'firstName'   => $addressCore->firstname,
                'lastName'    => $addressCore->lastname,
                'mobilePhone' => $addressCore->phone_mobile,
                'phone'       => $addressCore->phone,
                'zipCode'     => $addressCore->postcode,
                'city'        => $addressCore->city,
                'street'      => $addressCore->address1.' '.$addressCore->address2,
                'address'     => json_decode(json_encode($addressCore)),
            ];
        }

        if (_PS_VERSION_ < 1.7) {
            return [
                'dni'         => $addressCore->dni,
                'firstName'   => $addressCore->firstname,
                'lastName'    => $addressCore->lastname,
                'mobilePhone' => $addressCore->phone_mobile,
                'phone'       => $addressCore->phone,
                'zipCode'     => $addressCore->postcode,
                'city'        => $addressCore->city,
                'street'      => $addressCore->address1.' '.$addressCore->address2,
                'address'     => json_decode(json_encode($addressCore)),
            ];
        }
        return [];
    }
}
