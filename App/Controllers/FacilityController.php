<?php

namespace App\Controllers;

use App\Models\Facility;
use PDO;

class FacilityController extends BaseController
{

    public function create()
    {
        $facility = new Facility;
        $facility->setName($_POST['name']);

        $query = "INSERT INTO facilities (name) VALUES ('" . $facility->getName() . "')";
        $this->db->executeQuery($query);
    }
}
