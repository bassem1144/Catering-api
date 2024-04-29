<?php

use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{

    public function setUp(): void
    {
        # Turn on error reporting
        error_reporting(E_ALL);
    }

    public function testApiEndpoint()
    {
        // Api url
        $url = 'http://localhost/Catering_api/api/facilities/';

        // Make a GET request to the API endpoint
        $response = file_get_contents($url);

        // Check if the response is not empty
        $this->assertNotEmpty($response);

        // Check if the response is not false
        $this->assertNotFalse($response);
    }

    public function testReadFacility()
    {
        // Api url
        $url = 'http://localhost/Catering_api/api/facilities/1';

        // Make a GET request to read the details of a facility
        $response = file_get_contents($url);

        // Check if the response is not empty
        $this->assertNotEmpty($response);

        // Check if the response is not false
        $this->assertNotFalse($response);

        // Decode the JSON response
        $responseData = json_decode($response, true);

        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('city', $responseData);
        $this->assertArrayHasKey('tag_names', $responseData);
    }

    public function testCreateFacility()
    {
        // Api url
        $url = 'http://localhost/Catering_api/api/facilities';

        // Sample data to create a new facility
        $data = [
            'name' => 'Test Facility',
            'description' => 'This is a test facility',
            'city' => 'Test City',
            'tags' => ['Test Tag 1', 'Test Tag 2'],
        ];

        // Create a new facility using a POST request
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data),
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        // Check if the result is not empty
        $this->assertNotEmpty($result);

        // Check if the result is not false
        $this->assertNotFalse($result);
    }

    public function testUpdateFacility()
    {
        // Api url
        $url = 'http://localhost/Catering_api/api/facilities/1';

        // Sample data to update an existing facility
        $data = [
            'name' => 'Updated Facility',
            'description' => 'This is an updated facility',
            'city' => 'Updated City',
            'tags' => ['Updated Tag 1', 'Updated Tag 2'],
        ];

        // Update an existing facility using a PUT request
        $options = [
            'http' => [
                'method' => 'PUT',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data),
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        // Check if the result is not empty
        $this->assertNotEmpty($result);

        // Check if the result is not false
        $this->assertNotFalse($result);
    }

    public function testDeleteFacility()
    {
        // Api url
        $url = 'http://localhost/Catering_api/api/facilities/10';

        // Delete an existing facility using a DELETE request
        $options = [
            'http' => [
                'method' => 'DELETE',
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        // Check if the result is not empty
        $this->assertNotEmpty($result);

        // Check if the result is not false
        $this->assertNotFalse($result);
    }

    public function testSearchFacilities()
    {

        // Construct the API endpoint URL
        $url = 'http://localhost/Catering_api/api/facilities?city=amsterdam';

        // Make a GET request to the API endpoint
        $response = file_get_contents($url);

        // Decode the JSON response
        $responseData = json_decode($response, true);

        // Assert that the response is not empty
        $this->assertNotEmpty($responseData);

        // Check if the city in the response matches the expected value "amsterdam"
        foreach ($responseData as $facility) {
            $this->assertEquals('Amsterdam', $facility['city']);
        }
        
    }
}
