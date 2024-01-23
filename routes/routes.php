<?php

use App\Controllers\IndexController;
use App\Controllers\FacilityController;

/** @var Bramus\Router\Router $router */

// Define routes here
$router->get('/api/test', IndexController::class . '@test');
$router->get('/', IndexController::class . '@test');

// Define routes for the facility controller
$router->post('/api/facilities', FacilityController::class . '@create'); // Create a facility and its tags
$router->get('/api/facilities/{id}', FacilityController::class . '@read'); // Read one facility, its location, and its tags
$router->get('/api/facilities', FacilityController::class . '@showAll'); // Read multiple facilities, their location, and their tags
$router->put('/api/facilities/{id}', FacilityController::class . '@update'); // Update a facility and its tags
$router->delete('/api/facilities/{id}', FacilityController::class . '@delete'); // Delete a facility and its tags
