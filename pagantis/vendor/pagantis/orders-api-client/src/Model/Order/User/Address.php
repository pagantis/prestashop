<?php

namespace Pagantis\OrdersApiClient\Model\Order\User;

use Pagantis\OrdersApiClient\Model\AbstractModel;

/**
 * Class Address
 * @package Pagantis\OrdersApiClient\Model\Order\User
 */
class Address extends AbstractModel
{
    /**
     * @var string $address the street name with the address details.
     */
    protected $address;

    /**
     * @var string $city the city name
     */
    protected $city;

    /**
     * @var string $countryCode the country code ES, FR, PT, IT
     */
    protected $countryCode;

    /**
     * @var string $fullName the full name of the user, including 2 sur names
     */
    protected $fullName;

    /**
     * @var string $zipCode $the zipCode of the address.
     */
    protected $zipCode;

    /**
     * @var string $fixPhone Fix Phone of the user
     */
    protected $fixPhone;

    /**
     * @var string $mobilePhone Mobile phone of the user
     */
    protected $mobilePhone;

    /**
     * @var string $taxId User Tax Id.
     */
    protected $taxId;

    /**
     * @var string $nationalId User National Id.
     */
    protected $nationalId;

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     *
     * @return Address
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     *
     * @return Address
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param $fullName
     *
     * @return $this
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     *
     * @return Address
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getFixPhone()
    {
        return $this->fixPhone;
    }

    /**
     * @param string $fixPhone
     *
     * @return Address
     */
    public function setFixPhone($fixPhone)
    {
        $this->fixPhone = $fixPhone;

        return $this;
    }

    /**
     * @return string
     */
    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }

    /**
     * @param string $mobilePhone
     *
     * @return Address
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = $mobilePhone;

        return $this;
    }
    /**
     * @return string
     */
    public function getTaxId()
    {
        return $this->taxId;
    }

    /**
     * @param string $taxId
     *
     * @return Address
     */
    public function setTaxId($taxId)
    {
        $this->taxId = $taxId;

        return $this;
    }

    /**
     * @return string
     */
    public function getNationalId()
    {
        return $this->nationalId;
    }

    /**
     * @param string $nationalId
     *
     * @return Address
     */
    public function setNationalId($nationalId)
    {
        $this->nationalId = $nationalId;

        return $this;
    }
}
