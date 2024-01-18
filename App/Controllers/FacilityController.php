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
        $facility->setCreationDate();

        $query = "INSERT INTO facilities (name , creation_date) VALUES ('" . $facility->getName() . "', '" . $facility->getCreationDate() . "')";
        
        $this->db->executeQuery($query);

        var_dump($query);
    }
}
