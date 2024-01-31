<?php

namespace App\Models;

use PDO;
use App\Models\Tag;
use App\Models\Location;
use App\Plugins\Di\Injectable;

class Facility extends Injectable
{
    private $name;
    private $location;
    private $tags = [];

    //
    private $tagModel;
    private $locationModel;

    public function __construct()
    {
        $this->tagModel =  new Tag;
        $this->locationModel = new Location;
    }

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


    public function getFacilityById($facilityId)
    {
        try {
            // Fetch data for the specified facility
            $query = "SELECT facilities.*, locations.*, GROUP_CONCAT(tags.tag_name SEPARATOR ', ') as tag_names
                      FROM facilities
                      LEFT JOIN locations ON facilities.location_id = locations.location_id
                      LEFT JOIN facility_tags ON facilities.facility_id = facility_tags.facility_id
                      LEFT JOIN tags ON facility_tags.tag_id = tags.tag_id
                      WHERE facilities.facility_id = ?
                      GROUP BY facilities.facility_id";

            $result = $this->db->executeQuery($query, [$facilityId]);

            // Check if there are any results
            if ($result !== false) {
                return $result->fetch(PDO::FETCH_ASSOC);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            // Log or handle the database error
            throw new Exception('Database Error: ' . $e->getMessage());
        } catch (Exception $e) {
            // Log or handle other errors
            throw new Exception('Error: ' . $e->getMessage());
        }
    }

    public function createFacility(Facility $facility, Location $location, $tags)
    {
        // Insert the location into the database
        $locationId = $this->locationModel->insertLocation($location);

        // Insert the facility into the database with the associated location ID
        $facilityId = $this->insertFacility($facility, $locationId);

        // Handle tags
        $this->handleTags($facilityId, $tags);

        return $facilityId;
    }

    private function insertFacility(Facility $facility, $locationId)
    {
        $query = "INSERT INTO facilities (name, location_id) VALUES (?, ?)";
        $bind = [$facility->getName(), $locationId];
        $this->db->executeQuery($query, $bind);

        return $this->db->getLastInsertedId();
    }

    private function handleTags($facilityId, $tags)
    {
        if (isset($tags)) {
            $tagsArray = explode(",", $tags);

            foreach ($tagsArray as $tagName) {
                // Check if the tag already exists
                $tagId = $this->tagModel->getTagIdByName($tagName);

                if (!$tagId) {
                    // If tag doesn't exist, create a new tag
                    $tagId = $this->tagModel->insertTag($tagName);
                }

                // Insert the association into the Facility_Tags table
                $this->insertFacilityTag($facilityId, $tagId);
            }
        }
    }

    private function insertFacilityTag($facilityId, $tagId)
    {
        $query = "INSERT INTO facility_tags (facility_id, tag_id) VALUES (?, ?)";
        $bind = [$facilityId, $tagId];
        $this->db->executeQuery($query, $bind);
    }

    public function updateFacility($facilityId, $formData)
    {
        try {
            // Define update query for facilities
            $facilityUpdateQuery = "UPDATE facilities SET name = ? WHERE facility_id = ?";
            $facilityBind = [$formData['name'], $facilityId];

            // Execute the update query for facilities
            $this->db->executeQuery($facilityUpdateQuery, $facilityBind);

            // Define update query for locations
            $locationUpdateQuery = "UPDATE locations 
                                    SET city = ?, address = ?, zip_code = ?, country_code = ?, phone_number = ? 
                                    WHERE location_id = ?";

            $locationBind = [
                $formData['city'],
                $formData['address'],
                $formData['zip_code'],
                $formData['country_code'],
                $formData['phone_number'],
                $facilityId
            ];

            // Execute the update query for locations
            $this->db->executeQuery($locationUpdateQuery, $locationBind);

            // Handle tags
            if (isset($formData['tags'])) {
                $tagsArray = explode(",", $formData['tags']);

                // Delete existing tags for the facility
                $deleteTagsQuery = "DELETE FROM facility_tags WHERE facility_id = ?";
                $this->db->executeQuery($deleteTagsQuery, [$facilityId]);

                foreach ($tagsArray as $tagName) {
                    // Check if the tag already exists
                    $tagQuery = "SELECT tag_id FROM tags WHERE tag_name = ?";
                    $tagBind = [$tagName];

                    $existingTagId = $this->db->executeQuery($tagQuery, $tagBind)->fetchColumn();

                    if ($existingTagId) {
                        // If tag exists, use the existing tag
                        $tag = new Tag;
                        $tag->setId($existingTagId);
                    } else {
                        // If tag doesn't exist, create a new tag
                        $tag = new Tag;
                        $tag->setName($tagName);

                        // Insert the tag into the database
                        $tagQuery = "INSERT INTO tags (tag_name) VALUES (?)";
                        $tagBind = [$tag->getName()];
                        $this->db->executeQuery($tagQuery, $tagBind);

                        // Get the tag ID from the last inserted row
                        $tag->setId($this->db->getLastInsertedId());
                    }

                    // Insert the association into the Facility_Tags table
                    $facilityTagQuery = "INSERT INTO facility_tags (facility_id, tag_id) VALUES (?, ?)";
                    $facilityTagBind = [$facilityId, $tag->getId()];
                    $this->db->executeQuery($facilityTagQuery, $facilityTagBind);
                }
            }

            // Return a success message
            return ['message' => 'Facility updated successfully!'];
        } catch (PDOException $e) {
            // Handle database errors 
            throw new Exception('Database Error: ' . $e->getMessage());
        } catch (Exception $e) {
            // Handle other errors
            throw new Exception('Error: ' . $e->getMessage());
        }
    }

    public function deleteFacility($facilityId)
    {
        try {
            // Get the list of tags associated with the facility
            $facilityTagsQuery = "SELECT tag_id FROM facility_tags WHERE facility_id = ?";
            $facilityTags = $this->db->executeQuery($facilityTagsQuery, [$facilityId])->fetchAll(PDO::FETCH_COLUMN);

            // Delete facility_tags entries associated with the facility
            $deleteFacilityTagsQuery = "DELETE FROM facility_tags WHERE facility_id = ?";
            $this->db->executeQuery($deleteFacilityTagsQuery, [$facilityId]);

            // Delete the facility entry
            $deleteFacilityQuery = "DELETE FROM facilities WHERE facility_id = ?";
            $this->db->executeQuery($deleteFacilityQuery, [$facilityId]);

            // Check and delete tags that are not associated with any other facility
            foreach ($facilityTags as $tagId) {
                $checkTagQuery = "SELECT COUNT(*) FROM facility_tags WHERE tag_id = ?";
                $tagUsageCount = $this->db->executeQuery($checkTagQuery, [$tagId])->fetchColumn();

                if ($tagUsageCount == 0) {
                    // If the tag is not associated with any other facility, delete it
                    $deleteTagQuery = "DELETE FROM tags WHERE tag_id = ?";
                    $this->db->executeQuery($deleteTagQuery, [$tagId]);
                }
            }

            // Return a success message
            return ['message' => 'Facility and its tags deleted successfully!'];
        } catch (PDOException $e) {
            // Handle database errors 
            throw new Exception('Database Error: ' . $e->getMessage());
        } catch (Exception $e) {
            // Handle other errors
            throw new Exception('Error: ' . $e->getMessage());
        }
    }


    public function searchFacilities($name, $city, $tagName)
    {
        try {
            // SQL query to search for facilities
            $query = "SELECT facilities.*, locations.city, GROUP_CONCAT(tags.tag_name) as tag_names
                      FROM facilities
                      JOIN locations ON facilities.location_id = locations.location_id
                      LEFT JOIN facility_tags ON facilities.facility_id = facility_tags.facility_id
                      LEFT JOIN tags ON facility_tags.tag_id = tags.tag_id
                      WHERE 1";

            // Initialize an array to store bind values
            $bind = [];

            // Add conditions to the query and bind values based on provided parameters
            if ($name !== '') {
                $query .= " AND facilities.name LIKE :name";
                $bind[':name'] = "%$name%";
            }

            if ($city !== '') {
                $query .= " AND locations.city LIKE :city";
                $bind[':city'] = "%$city%";
            }

            if ($tagName !== '') {
                $query .= " AND tags.tag_name LIKE :tagName";
                $bind[':tagName'] = "%$tagName%";
            }

            // Complete the query
            $query .= " GROUP BY facilities.facility_id";

            // Execute the query
            $result = $this->db->executeQuery($query, $bind)->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } catch (PDOException $e) {
            // Handle database errors 
            throw new Exception('Database Error: ' . $e->getMessage());
        } catch (Exception $e) {
            // Handle other errors
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
