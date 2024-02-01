<?php

namespace App\Models;

use App\Plugins\Di\Injectable;

class Tag extends Injectable
{
    private $id;
    private $name;

    /**
     * Get the tag ID by tag name.
     *
     * @param string $tagName The name of the tag.
     *
     * @return int|null Returns the tag ID if found, or null if not found.
     */
    public function getTagIdByName(string $tagName)
    {
        $query = "SELECT tag_id FROM tags WHERE tag_name = ?";
        $bind = [$tagName];
        return $this->db->executeQuery($query, $bind)->fetchColumn();
    }

    /**
     * Insert a new tag into the database.
     *
     * @param string $tagName The name of the tag.
     *
     * @return int Returns the ID of the newly inserted tag.
     */
    public function insertTag(string $tagName)
    {
        $query = "INSERT INTO tags (tag_name) VALUES (?)";
        $bind = [$tagName];
        $this->db->executeQuery($query, $bind);

        return $this->db->getLastInsertedId();
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
     * Set the name of the facility.
     *
     * @param string $name The name of the facility.
     *
     * @return Tag Returns the Tag object.
     */
    public function setName(string $name): Tag
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the ID of the facility.
     *
     * @return int The ID of the facility.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the ID of the facility.
     *
     * @param int $id The ID of the facility.
     *
     * @return Tag Returns the Tag object.
     */
    public function setId(int $id): Tag
    {
        $this->id = $id;
        return $this;
    }
}