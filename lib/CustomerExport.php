<?php

class CustomerExport
{
    /**
     * @param CustomerCore $customerCore
     *
     * @return array
     */
    public static function export(CustomerCore $customerCore)
    {
        return [
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
}