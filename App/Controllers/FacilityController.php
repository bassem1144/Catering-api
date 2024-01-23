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

            // Get the facility ID from the last inserted row
            $facilityId = $this->db->getLastInsertedId();











            // Handle tags
            $tagsArray = explode(", ", $_POST['tags']);

            if (isset($tagsArray) && is_array($tagsArray)) {
                foreach ($tagsArray as $tagName) {
                    // Check if the tag already exists
                    $tagQuery = "SELECT tag_id FROM tags WHERE tag_name = ?";
                    $tagBind = [$tagName];

                    $existingTagId = $this->db->executeQuery($tagQuery, $tagBind)->fetchColumn();

                    if ($existingTagId) {
                        // If tag exists, use the existing tag
                        $tag = new Tag;
                        $tag->setId($existingTagId);
                    } else {
                        // If tag doesn't exist, create a new tag
                        $tag = new Tag;
                        $tag->setName($tagName);

                        // Insert the tag into the database
                        $tagQuery = "INSERT INTO tags (tag_name) VALUES (?)";
                        $tagBind = [$tag->getName()];
                        $this->db->executeQuery($tagQuery, $tagBind);

                        // Get the tag ID from the last inserted row
                        $tag->setId($this->db->getLastInsertedId());
                    }

                    // Associate the tag with the facility
                    $facility->addTag($tag);

                    // Insert the association into the FacilityTags table
                    $facilityTagQuery = "INSERT INTO facilitytags (facility_id, tag_id) VALUES (?, ?)";
                    $facilityTagBind = [$facilityId, $tag->getId()];
                    $this->db->executeQuery($facilityTagQuery, $facilityTagBind);
                }
            }













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
