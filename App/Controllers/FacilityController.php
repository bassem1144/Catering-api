<?php

namespace App\Controllers;

use App\Models\Facility;
use PDO;

class FacilityController extends BaseController
{

    public function create()
    {

        // Assuming Facility class has a setName method
        $facility = new Facility;
        $facility->setName($_POST['name']);

        // Assuming you have a method like executeQuery that takes an SQL query and executes it
        $query = "INSERT INTO facilities (name) VALUES ('" . $facility->getName() . "')";
        $this->db->executeQuery($query);
    }
}
