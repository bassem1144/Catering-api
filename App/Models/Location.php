<?php

namespace App\Models;

class Location
{
    private $city;
    private $address;
    private $zipCode;
    private $countryCode;
    private $phoneNumber;

    public function setCity(string $city): Location
    {
        $this->city = $city;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setAddress(string $address): Location
    {
        $this->address = $address;
        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setZipCode(string $zipCode): Location
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setCountryCode(string $countryCode): Location
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setPhoneNumber(string $phoneNumber): Location
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }
}
