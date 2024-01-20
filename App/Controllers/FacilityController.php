<?php

namespace App\Controllers;

use App\Models\Facility;
use App\Models\Location;
use App\Models\Tag;

class FacilityController extends BaseController
{

    public function create()
    {
        try {
            $facility = new Facility;
            $facility->setName($_POST['name']);
            $facility->setCreationDate();

            $location = new Location;
            $location->setCity($_POST['city']);
            $location->setAddress($_POST['address']);
            $location->setZipCode($_POST['zip_code']);
            $location->setCountryCode($_POST['country_code']);
            $location->setPhoneNumber($_POST['phone_number']);

            // Associate the location with the facility
            $facility->setLocation($location);

            // Begin a transaction
            $this->db->beginTransaction();

            // Insert the location into the database
            $locationQuery = "INSERT INTO locations (city, address, zip_code, country_code, phone_number) VALUES (?, ?, ?, ?, ?)";
            $locationBind = [$location->getCity(), $location->getAddress(), $location->getZipCode(), $location->getCountryCode(), $location->getPhoneNumber()];
            $this->db->executeQuery($locationQuery, $locationBind);

            // Get the location ID from the last inserted row
            $locationId = $this->db->getLastInsertedId();

            // Insert the facility into the database with the associated location ID
            $facilityQuery = "INSERT INTO facilities (name, creation_date, location_id) VALUES (?, ?, ?)";
            $facilityBind = [$facility->getName(), $facility->getCreationDate(), $locationId];
            $this->db->executeQuery($facilityQuery, $facilityBind);

            // Commit the transaction
            $this->db->commit();
            echo "Facility and Location created successfully!";
        } catch (PDOException $e) {
            // Rollback the transaction in case of a database error
            $this->db->rollBack();

            // Handle database errors 
            echo "Database Error: " . $e->getMessage();
        } catch (Exception $e) {
            // Handle other errors
            echo "Error: " . $e->getMessage();
        }
    }

}
