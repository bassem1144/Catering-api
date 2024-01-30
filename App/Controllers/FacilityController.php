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
            $this->respondWithJson($facilities, 200);
        } catch (PDOException $e) {
            // Handle database errors
            $this->handleError($e, 'Database Error');
        } catch (Exception $e) {
            // Handle other errors
            $this->handleError($e, 'Error');
        }
    }

    public function read($facilityId)
    {
        try {
            // Get facility data from the model
            $facilityData = $this->facilityModel->getFacilityById($facilityId);

            // Return the data as JSON with a 200 OK status code
            if ($facilityData) {
                $this->respondWithJson($facilityData, 200);
            } else {
                // show message when no facility is found
                $this->respondWithJson(['error' => 'Facility not found.'], 404);
            }
        } catch (PDOException $e) {
            // Handle database errors
            $this->handleError($e, 'Database Error');
        } catch (Exception $e) {
            // Handle other errors
            $this->handleError($e, 'Error');
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
            $this->respondWithJson(['message' => 'Facility, Location, and tags created successfully!'], 201);
        } catch (PDOException $e) {
            // Rollback the transaction in case of a database error
            $this->db->rollBack();

            // Handle database errors 
            $this->handleError($e, 'Database Error');
        } catch (Exception $e) {
            // Handle other errors
            $this->handleError($e, 'Error');
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

            echo json_encode($result);
        } catch (Exception $e) {
            // Handle errors
            $this->handleError($e, 'Error');
        }
    }

    public function delete($facilityId)
    {
        try {
            // Call the model method to handle database deletion
            $result = $this->facilityModel->deleteFacility($facilityId);

            // Return the result as JSON with the appropriate HTTP status code
            header('Content-Type: application/json');

            echo json_encode($result);
        } catch (Exception $e) {
            // Handle other errors
            $this->handleError($e, 'Error');
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
                $this->respondWithJson($result, 200);
            } else {
                // Show message when no facilities are found
                $this->respondWithJson(['error' => 'No facilities found'], 404);
            }
        } catch (Exception $e) {
            // Handle errors
            $this->handleError($e, 'Error');
        }
    }

    // Method to respond with JSON and an HTTP status code
    private function respondWithJson($data, $statusCode)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    // Method to handle errors and respond with JSON
    private function handleError($e, $errorMessage)
    {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => $errorMessage . ': ' . $e->getMessage()]);
    }
}
