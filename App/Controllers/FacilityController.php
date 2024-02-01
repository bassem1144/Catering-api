<?php

namespace App\Controllers;

use Exception;
use PDOException;
use App\Models\Tag;
use App\Models\Facility;
use App\Models\Location;

class FacilityController extends BaseController
{

    private Facility $facilityModel;
    private Tag $tagModel;

    /**
     * Constructor for the FacilityController class.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->facilityModel = new Facility();
        $this->tagModel = new Tag();
    }

    /**
     * Retrieve all facilities and their details.
     * 
     * @throws PDOException if a database error occurs.
     * @throws Exception If an error occurs during the process.
     * 
     * @return array An array containing details of all facilities.
     */
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

    /**
     * Retrieve details of a specific facility by ID.
     *
     * @param int $facilityId The ID of the facility to retrieve.
     *
     * @throws PDOException if a database error occurs.
     * @throws Exception If an error occurs during the process.
     * 
     * @return array An array containing details of the facility.
     */
    public function read(int $facilityId)
    {
        try {
            // Get facility data from the model
            $facilityData = $this->facilityModel->getFacilityById($facilityId);

            // Return the data as JSON with a 200 OK status code
            if ($facilityData) {
                $this->respondWithJson($facilityData, 200);
            } else {
                // Show message when no facility is found
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

    /**
     * Create a new facility, associated location, and tags.
     *
     * @throws PDOException if a database error occurs.
     * @throws Exception for other errors.
     * 
     * @return void
     */
    public function create()
    {
        try {
            // Get JSON data from the request body
            $jsonInput = file_get_contents('php://input');
            $jsonData = json_decode($jsonInput, true);

            // Create a new Facility
            $facility = new Facility;
            $facility->setName($jsonData['name']);

            // Create a new Location
            $location = new Location;
            $location->setCity($jsonData['location']['city']);
            $location->setAddress($jsonData['location']['address']);
            $location->setZipCode($jsonData['location']['zip_code']);
            $location->setCountryCode($jsonData['location']['country_code']);
            $location->setPhoneNumber($jsonData['location']['phone_number']);

            // Associate the location with the facility
            $facility->setLocation($location);

            // Begin a transaction
            $this->db->beginTransaction();

            // Call the model methods to handle database insertion
            $facilityId = $this->facilityModel->createFacility($facility, $location, $jsonData['tags'], $this->tagModel);

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

    /**
     * Update a facility and its tags.
     *
     * @param int $facilityId The ID of the facility to update.
     *
     * @throws Exception If an error occurs during the process.
     * 
     * @return void
     */
    public function update(int $facilityId)
    {
        try {
            // Get the updated data from the API request
            $jsonInput = file_get_contents('php://input');
            $jsonData = json_decode($jsonInput, true);

            // Call the model method to handle database update
            $result = $this->facilityModel->updateFacility($facilityId, $jsonData);

            // Return the result as JSON with 200 HTTP status code
            $this->respondWithJson($result, 200);
        } catch (Exception $e) {
            // Handle errors
            $this->handleError($e, 'Error');
        }
    }

    /**
     * Delete a facility and its tags.
     *
     * @param int $facilityId The ID of the facility to delete.
     *
     * @throws Exception If an error occurs during the process.
     * 
     * @return void
     */
    public function delete(int $facilityId)
    {
        try {
            // Call the model method to handle database deletion
            $result = $this->facilityModel->deleteFacility($facilityId);

            // Return the result as JSON with 200 HTTP status code
            $this->respondWithJson($result, 200);
        } catch (Exception $e) {
            // Handle other errors
            $this->handleError($e, 'Error');
        }
    }

    /**
     * Search for facilities by name, city or tag.
     *
     * @throws PDOException if a database error occurs.
     * @throws Exception If an error occurs during the process.
     * 
     * @return void
     */
    public function searchFacilities()
    {
        try {
            // Get query parameters from the request
            $name = $_GET['name'] ?? '';
            $city = $_GET['city'] ?? '';
            $tagName = $_GET['tag'] ?? '';

            // Call the model method to handle database search
            $result = $this->facilityModel->searchFacilities($name, $city, $tagName);

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

    /**
     * Private function to respond with JSON data and an HTTP status code.
     * 
     * @param array $data The data to return.
     * @param int $statusCode The HTTP status code to return.
     */
    private function respondWithJson($data, $statusCode)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }

    /**
     * Private function to handle errors.
     * 
     * @param Exception $e The exception that was thrown.
     * @param string $errorMessage The error message to return.
     */
    private function handleError($e, $errorMessage)
    {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => $errorMessage . ': ' . $e->getMessage()]);
    }
}
