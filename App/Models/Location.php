<?php

namespace App\Models;

use App\Plugins\Di\Injectable;

class Location extends Injectable
{
    private $city;
    private $address;
    private $zipCode;
    private $countryCode;
    private $phoneNumber;

    /**
     * Insert a new location into the database.
     *
     * @param Location $location The Location object containing information about the new location.
     *
     * @return int The last inserted ID for the new location.
     */
    public function insertLocation(Location $location)
    {
        $query = "INSERT INTO locations (city, address, zip_code, country_code, phone_number) VALUES (?, ?, ?, ?, ?)";
        $bind = [$location->getCity(), $location->getAddress(), $location->getZipCode(), $location->getCountryCode(), $location->getPhoneNumber()];
        $this->db->executeQuery($query, $bind);

        return $this->db->getLastInsertedId();
    }

    /**
     * Set the city of the location.
     *
     * @param string $city The new city for the location.
     *
     * @return Location Returns the current instance of Location for method chaining.
     */
    public function setCity(string $city): Location
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Get the city of the location.
     *
     * @return string The city of the location.
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Set the address of the location.
     *
     * @param string $address The new address for the location.
     *
     * @return Location Returns the current instance of Location for method chaining.
     */
    public function setAddress(string $address): Location
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Get the address of the location.
     *
     * @return string The address of the location.
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Set the zip code of the location.
     *
     * @param string $zipCode The new zip code for the location.
     *
     * @return Location Returns the current instance of Location for method chaining.
     */
    public function setZipCode(string $zipCode): Location
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    /**
     * Get the zip code of the location.
     *
     * @return string The zip code of the location.
     */
    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    /**
     * Set the country code of the location.
     *
     * @param string $countryCode The new country code for the location.
     *
     * @return Location Returns the current instance of Location for method chaining.
     */
    public function setCountryCode(string $countryCode): Location
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * Get the country code of the location.
     *
     * @return string The country code of the location.
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Set the phone number of the location.
     *
     * @param string $phoneNumber The new phone number for the location.
     *
     * @return Location Returns the current instance of Location for method chaining.
     */
    public function setPhoneNumber(string $phoneNumber): Location
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * Get the phone number of the location.
     *
     * @return string The phone number of the location.
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }
}
