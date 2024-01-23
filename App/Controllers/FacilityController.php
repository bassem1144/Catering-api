<?php

namespace App\Controllers;

use PDO;
use App\Models\Tag;
use App\Models\Facility;
use App\Models\Location;

class FacilityController extends BaseController
{

    public function readAll()
    {
        // Fetch all facilities with their locations and tags
        $query = "SELECT facilities.*, locations.*, GROUP_CONCAT(tags.tag_name SEPARATOR ', ') as tag_names
                  FROM facilities
                  LEFT JOIN locations ON facilities.location_id = locations.location_id
                  LEFT JOIN facilitytags ON facilities.facility_id = facilitytags.facility_id
                  LEFT JOIN tags ON facilitytags.tag_id = tags.tag_id
                  GROUP BY facilities.facility_id";

        $result = $this->db->executeQuery($query);

        // Check if there are any results
        if ($result !== false) {
            $facilities = $result->fetchAll(PDO::FETCH_ASSOC);

            // Display the data 
            foreach ($facilities as $facility) {
                echo "Facility ID: {$facility['facility_id']}\n";
                echo "Name: {$facility['name']}\n";
                echo "Creation Date: {$facility['creation_date']}\n";
                echo "Location: {$facility['city']}, {$facility['address']}, {$facility['zip_code']}, {$facility['country_code']}\n";
                // Check if the facility has any tags
                if (!empty($facility['tag_names'])) {
                    echo "Tags: {$facility['tag_names']}\n";
                } else {
                    echo "No tags\n";
                }
                echo "\n";
            }
        } else {
            // Handle error or show a message if no facilities are found
            echo "No facilities found.";
        }
    }

    public function read($facilityId)
    {
        // Fetch data for the specified facility
        $query = "SELECT facilities.*, locations.*, GROUP_CONCAT(tags.tag_name SEPARATOR ', ') as tag_names
                  FROM facilities
                  LEFT JOIN locations ON facilities.location_id = locations.location_id
                  LEFT JOIN facilitytags ON facilities.facility_id = facilitytags.facility_id
                  LEFT JOIN tags ON facilitytags.tag_id = tags.tag_id
                  WHERE facilities.facility_id = ?
                  GROUP BY facilities.facility_id";

        $result = $this->db->executeQuery($query, [$facilityId]);

        // Check if there are any results
        if ($result !== false) {
            $facilityData = $result->fetch(PDO::FETCH_ASSOC);

            // Display the data
            if ($facilityData) {
                echo "Facility ID: {$facilityData['facility_id']}\n";
                echo "Name: {$facilityData['name']}\n";
                echo "Creation Date: {$facilityData['creation_date']}\n";
                echo "Location: {$facilityData['city']}, {$facilityData['address']}, {$facilityData['zip_code']}, {$facilityData['country_code']}\n";

                // Check if there are tags associated with the facility
                if (!empty($facilityData['tag_names'])) {
                    echo "Tags: {$facilityData['tag_names']}\n";
                } else {
                    echo "No tags associated with this facility.\n";
                }
            } else {
                echo "Facility not found.";
            }
        } else {
            // Handle error
            echo "Error fetching facility data.";
        }
    }

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
            if (isset($_POST['tags'])) {
                $tagsArray = explode(", ", $_POST['tags']);
            } else {
                $tagsArray = null;
            }

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
            echo "Facility , Location and tags created successfully!";
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
