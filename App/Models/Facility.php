<?php

namespace App\Models;

use App\Plugins\Di\Injectable;
use App\Models\Tag;
use PDO;

class Facility extends Injectable
{
    private $id;
    private $name;
    private $location;
    private $tags = [];


    public function getAllFacilities()
    {
        try {
            // Fetch all facilities with their locations and tags
            $query = "SELECT facilities.*, locations.*, GROUP_CONCAT(tags.tag_name SEPARATOR ', ') as tag_names
                  FROM facilities
                  LEFT JOIN locations ON facilities.location_id = locations.location_id
                  LEFT JOIN facility_tags ON facilities.facility_id = facility_tags.facility_id
                  LEFT JOIN tags ON facility_tags.tag_id = tags.tag_id
                  GROUP BY facilities.facility_id";

            $result = $this->db->executeQuery($query);

            // Check if there are any results
            if ($result) {
                return $result->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return [];
            }
        } catch (PDOException $e) {
            // Log or handle the database error
            throw new Exception('Database Error: ' . $e->getMessage());
        } catch (Exception $e) {
            // Log or handle other errors
            throw new Exception('Error: ' . $e->getMessage());
        }
    }

    public function setName(string $name): Facility
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setLocation(Location $location): Facility
    {
        $this->location = $location;
        return $this;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function addTag(Tag $tag): Facility
    {
        $this->tags[] = $tag;
        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}
