<?php

namespace App\Controllers;

use PDO;
use App\Models\Tag;
use App\Models\Facility;
use App\Models\Location;

class FacilityController extends BaseController
{

    private $facilityModel;
    private $locationModel;
    private $tagModel;

    public function __construct()
    {
        $this->facilityModel = new Facility();
        $this->locationModel = new Location();
        $this->tagModel = new Tag();
    }


    public function readAll()
    {
        try {
            // Get facilities from the model
            $facilities = $this->facilityModel->getAllFacilities();

            // Return the data as JSON with a 200 OK status code
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode($facilities);
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
            // Get facility data from the model
            $facilityData = $this->facilityModel->getFacilityById($facilityId);

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

            // Call the model methods to handle database insertion
            $facilityId = $this->facilityModel->createFacility($facility, $location, $_POST['tags'], $this->tagModel);

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

            // Call the model method to handle database update
            $result = $this->facilityModel->updateFacility($facilityId, $formData);

            // Return the result as JSON with the appropriate HTTP status code
            header('Content-Type: application/json');

            if (isset($result['error'])) {
                http_response_code(500);
            } else {
                http_response_code(200);
            }

            echo json_encode($result);
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
            // Call the model method to handle database deletion
            $result = $this->facilityModel->deleteFacility($facilityId);

            // Return the result as JSON with the appropriate HTTP status code
            header('Content-Type: application/json');

            if (isset($result['error'])) {
                http_response_code(500);
            } else {
                http_response_code(200);
            }

            echo json_encode($result);
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

            // Call the model method to handle database search
            $result = $this->facilityModel->searchFacilities($name, $city, $tagName);

            // Return the result as JSON with the appropriate HTTP status code
            header('Content-Type: application/json');

            if ($result) {
                // Return the result as JSON with a 200 OK status code
                http_response_code(200);
                echo json_encode($result);
            } else {
                // Handle error when no facilities are found
                http_response_code(404);
                echo json_encode(['error' => 'No facilities found']);
            }
        } catch (Exception $e) {
            // Handle other errors
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    }
}
