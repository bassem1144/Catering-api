<?php

namespace App\Models;

use PDO;
use Exception;
use PDOException;
use App\Models\Tag;
use App\Models\Location;
use App\Plugins\Di\Injectable;

class Facility extends Injectable
{
    private $name;
    private $location;
    private $tags = [];

    private $tagModel;
    private $locationModel;

    /**
     * Constructor for the Facility class.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->tagModel =  new Tag;
        $this->locationModel = new Location;
    }

    /**
     * Retrieve facilities based on optional filters or all facilities if no filters are provided.
     * 
     * 
     * @param string $name The name of the facility to search for.
     * @param string $city The city of the facility to search for.
     * @param string $tagName The tag of the facility to search for.
     * 
     * @throws PDOException if a database error occurs.
     * @throws Exception for other errors.
     * 
     * @return array An array containing details of the facilities.
     */
    public function searchFacilities(string $name, string $city, string $tagName)
    {
        try {
            // SQL query to search for facilities
            $query =
                "SELECT facilities.*, locations.city, GROUP_CONCAT(tags.tag_name) as tag_names
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
                $query .= " AND facilities.facility_id IN (
                            SELECT facility_id FROM facility_tags
                            JOIN tags ON facility_tags.tag_id = tags.tag_id
                            WHERE tags.tag_name LIKE :tagName
                            )";
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
    public function getFacilityById(int $facilityId)
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

    /**
     * Create a new facility, associated location, and tags.
     *
     * @param Facility $facility The facility to create.
     * @param Location $location The location to create.
     * @param array $tags The tags to associate with the facility.
     *
     * @throws PDOException if a database error occurs.
     * @throws Exception for other errors.
     * 
     * @return int The ID of the newly created facility.
     */
    public function createFacility(Facility $facility, Location $location, array $tags)
    {
        // Insert the location into the database
        $locationId = $this->locationModel->insertLocation($location);

        // Insert the facility into the database with the associated location ID
        $facilityId = $this->insertFacility($facility, $locationId);

        // Handle tags
        $this->handleTags($facilityId, $tags);

        return $facilityId;
    }

    /**
     * Insert the facility into the database with the associated location ID.
     *
     * @param Facility $facility The facility to insert.
     * @param int $locationId The ID of the location to associate with the facility.
     *
     * @throws PDOException if a database error occurs.
     * @throws Exception for other errors.
     * 
     * @return int The ID of the newly created facility.
     */
    private function insertFacility(Facility $facility, int $locationId)
    {
        $query = "INSERT INTO facilities (name, location_id) VALUES (?, ?)";
        $bind = [$facility->getName(), $locationId];
        $this->db->executeQuery($query, $bind);

        return $this->db->getLastInsertedId();
    }

    /**
     * Handle tags for the facility.
     *
     * @param int $facilityId The ID of the facility to associate the tags with.
     * @param array $tags The tags to associate with the facility.
     *
     * @throws PDOException if a database error occurs.
     * @throws Exception for other errors.
     * 
     * @return void
     */
    private function handleTags(int $facilityId, array $tags)
    {
        if (isset($tags)) {

            foreach ($tags as $tagName) {
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

    /**
     * Insert the association into the Facility_Tags table.
     *
     * @param int $facilityId The ID of the facility to associate the tag with.
     * @param int $tagId The ID of the tag to associate with the facility.
     *
     * @throws PDOException if a database error occurs.
     * @throws Exception for other errors.
     * 
     * @return void
     */
    private function insertFacilityTag(int $facilityId, int $tagId)
    {
        $query = "INSERT INTO facility_tags (facility_id, tag_id) VALUES (?, ?)";
        $bind = [$facilityId, $tagId];
        $this->db->executeQuery($query, $bind);
    }

    /**
     * Update a facility and its tags.
     *
     * @param int $facilityId The ID of the facility to update.
     * @param array $formData The data to update the facility with.
     *
     * @throws PDOException if a database error occurs.
     * @throws Exception for other errors.
     * 
     * @return array The result of the update.
     */
    public function updateFacility(int $facilityId, array $formData)
    {
        try {
            // Define update query for facilities
            $facilityUpdateQuery = "UPDATE facilities SET name = ? WHERE facility_id = ?";
            $facilityBind = [$formData['name'], $facilityId];

            // Execute the update query for facilities
            $this->db->executeQuery($facilityUpdateQuery, $facilityBind);

            // Handle tags
            if (isset($formData['tags'])) {

                // Delete existing tags for the facility
                $deleteTagsQuery = "DELETE FROM facility_tags WHERE facility_id = ?";
                $this->db->executeQuery($deleteTagsQuery, [$facilityId]);

                foreach ($formData['tags'] as $tagName) {
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

    /**
     * Delete a facility and its tags.
     *
     * @param int $facilityId The ID of the facility to delete.
     *
     * @throws PDOException if a database error occurs.
     * @throws Exception for other errors.
     * 
     * @return array The result of the deletion.
     */
    public function deleteFacility(int $facilityId)
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

    /**
     * Set the name of the facility.
     *
     * @param string $name The new name for the facility.
     *
     * @return Facility Returns the current instance of Facility for method chaining.
     */
    public function setName(string $name): Facility
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name of the facility.
     *
     * @return string The name of the facility.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the location of the facility.
     *
     * @param Location $location The new location for the facility.
     *
     * @return Facility Returns the current instance of Facility for method chaining.
     */
    public function setLocation(Location $location): Facility
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Get the location of the facility.
     *
     * @return Location The location of the facility.
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * Add a tag to the facility.
     *
     * @param Tag $tag The tag to add to the facility.
     *
     * @return Facility Returns the current instance of Facility for method chaining.
     */
    public function addTag(Tag $tag): Facility
    {
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * Get the tags associated with the facility.
     *
     * @return array The tags associated with the facility.
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
