<?php

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
            return (object) [
                'id' => $customerCore->id,
                'gender' => $customerCore->id_gender,
                'email' => $customerCore->email,
                'dob' => $customerCore->birthday,
                'firstName' => $customerCore->firstname,
                'lastName' => $customerCore->lastname,
                'isGuest' => $customerCore->is_guest,
                'memberSince' => $customerCore->date_add,
                'customer' => json_decode(json_encode($customerCore)),
            ];
        }

        if (_PS_VERSION_ < 1.7) {
            return (object) [
                'id' => $customerCore->id,
                'gender' => $customerCore->id_gender,
                'email' => $customerCore->email,
                'dob' => $customerCore->birthday,
                'firstName' => $customerCore->firstname,
                'lastName' => $customerCore->lastname,
                'isGuest' => $customerCore->is_guest,
                'memberSince' => $customerCore->date_add,
                'customer' => json_decode(json_encode($customerCore)),
            ];
        }
        return (object) [];
    }
}
