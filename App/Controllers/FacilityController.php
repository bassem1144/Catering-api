<?php

namespace App\Controllers;

use PDO;
use Exception;
use PDOException;
use App\Models\Tag;
use App\Models\Facility;
use App\Models\Location;

class FacilityController extends BaseController
{

    public function readAll()
    {
        try {
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

                // Return the data as JSON with a 200 OK status code
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode($facilities);
            } else {
                // Handle error when no facility is found
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['error' => 'No facilities found.']);
            }
        } catch (PDOException $e) {
            // Handle database errors
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database Error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            // Handle other errors
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function read($facilityId)
    {
        try {
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

                // Return the data as JSON with a 200 OK status code
                if ($facilityData) {
                    header('Content-Type: application/json');
                    http_response_code(200);
                    echo json_encode($facilityData);
                } else {
                    // Handle error when no facility is found
                    header('Content-Type: application/json');
                    http_response_code(404);
                    echo json_encode(['error' => 'Facility not found.']);
                }
            } else {
                // Handle error
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['error' => 'Error fetching facility data.']);
            }
        } catch (PDOException $e) {
            // Handle database errors
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database Error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            // Handle other errors
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function create()
    {
        try {
            // Create a new Facility
            $facility = new Facility;
            $facility->setName($_POST['name']);
            $facility->setCreationDate();

            // Create a new Location
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
                $tagsArray = explode(",", $_POST['tags']);
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

            // Return a success message with a 201 Created status code
            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode(['message' => 'Facility, Location, and tags created successfully!']);
        } catch (PDOException $e) {
            // Rollback the transaction in case of a database error
            $this->db->rollBack();

            // Handle database errors 
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database Error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            // Handle other errors
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function update($facilityId)
    {
        try {
            // Get the updated data from the API request
            $putData = file_get_contents('php://input');
            parse_str($putData, $formData);

            // Define update queries and fields
            $updateQueries = [
                'name' => "UPDATE facilities SET name = ? WHERE facility_id = ?",
                'city' => "UPDATE locations SET city = ? WHERE location_id = ?",
                'address' => "UPDATE locations SET address = ? WHERE location_id = ?",
                'zip_code' => "UPDATE locations SET zip_code = ? WHERE location_id = ?",
                'country_code' => "UPDATE locations SET country_code = ? WHERE location_id = ?",
                'phone_number' => "UPDATE locations SET phone_number = ? WHERE location_id = ?",
            ];

            // loop through the fields and execute the update queries
            foreach ($updateQueries as $field => $query) {
                if (isset($formData[$field])) {
                    $this->db->executeQuery($query, [$formData[$field], $facilityId]);
                }
            }

            // Handle tags
            if (isset($formData['tags'])) {
                $tagsArray = explode(",", $formData['tags']);

                // Delete existing tags for the facility
                $deleteTagsQuery = "DELETE FROM facilitytags WHERE facility_id = ?";
                $this->db->executeQuery($deleteTagsQuery, [$facilityId]);

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

                    // Insert the association into the FacilityTags table
                    $facilityTagQuery = "INSERT INTO facilitytags (facility_id, tag_id) VALUES (?, ?)";
                    $facilityTagBind = [$facilityId, $tag->getId()];
                    $this->db->executeQuery($facilityTagQuery, $facilityTagBind);
                }
            }

            // Return a success message with a 200 OK status code
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['message' => 'Facility updated successfully!']);
        } catch (PDOException $e) {
            // Handle database errors 
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database Error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            // Handle other errors
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function delete($facilityId)
    {
        try {
            // Delete facilitytags entries associated with the facility
            $deleteFacilityTagsQuery = "DELETE FROM facilitytags WHERE facility_id = ?";
            $this->db->executeQuery($deleteFacilityTagsQuery, [$facilityId]);

            // Delete the facility entry
            $deleteFacilityQuery = "DELETE FROM facilities WHERE facility_id = ?";
            $this->db->executeQuery($deleteFacilityQuery, [$facilityId]);

            // Return a success message with a 200 OK status code
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['message' => 'Facility and its tags deleted successfully!']);
        } catch (PDOException $e) {
            // Handle database errors 
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database Error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            // Handle other errors
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function searchFacilities()
    {
        try {
            // Get query parameters from the request
            $name = $_GET['name'] ?? '';
            $city = $_GET['city'] ?? '';
            $tagName = $_GET['tag'] ?? '';

            // SQL query to search for facilities
            $query = "SELECT facilities.*, locations.city, GROUP_CONCAT(tags.tag_name) as tag_names
              FROM facilities
              JOIN locations ON facilities.location_id = locations.location_id
              LEFT JOIN facilitytags ON facilities.facility_id = facilitytags.facility_id
              LEFT JOIN tags ON facilitytags.tag_id = tags.tag_id
              WHERE 1";

            // Initialize an array to store bind values
            $bind = [];

            // Add conditions to the query and bind values based on provided parameters
            if ($name !== '') {
                $query .= " AND facilities.name LIKE :name";
                $bind[':name'] = "%$name%";
            }

            if ($city !== '') {
                $query .= " AND locations.city LIKE :city";
                $bind[':city'] = "%$city%";
            }

            if ($tagName !== '') {
                $query .= " AND tags.tag_name LIKE :tagName";
                $bind[':tagName'] = "%$tagName%";
            }

            // Complete the query
            $query .= " GROUP BY facilities.facility_id";

            // Execute the query
            $result = $this->db->executeQuery($query, $bind)->fetchAll(PDO::FETCH_ASSOC);

            // Check if there are any results
            if ($result) {
                // Return the data as JSON with a 200 OK status code
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode($result);
            } else {
                // Handle error when no facility is found
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['error' => 'No facilities found']);
            }
        } catch (PDOException $e) {
            // Handle database errors 
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Database Error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            // Handle other errors
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }


}
