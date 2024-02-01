<?php

use App\Controllers\IndexController;
use App\Controllers\FacilityController;

/** @var Bramus\Router\Router $router */

// Define routes here

// Search for a facility by name, description, or tag
$router->get('/api/facilities/search/', FacilityController::class . '@searchFacilities');

// Create a facility and its tags
$router->post('/api/facilities', FacilityController::class . '@create');

// Read details of one facility, including its location and tags
$router->get('/api/facilities/{id}', FacilityController::class . '@read');

// Read details of multiple facilities, including their locations and tags
$router->get('/api/facilities', FacilityController::class . '@readAll');

// Update details of a facility, including its tags
$router->put('/api/facilities/{id}', FacilityController::class . '@update');

// Delete a facility and its tags
$router->delete('/api/facilities/{id}', FacilityController::class . '@delete');
