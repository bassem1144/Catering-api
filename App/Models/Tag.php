<?php

namespace App\Models;

use App\Plugins\Di\Injectable;

class Tag extends Injectable
{
    private $id;
    private $name;

    public function getTagIdByName($tagName)
    {
        $query = "SELECT tag_id FROM tags WHERE tag_name = ?";
        $bind = [$tagName];
        return $this->db->executeQuery($query, $bind)->fetchColumn();
    }

    public function insertTag($tagName)
    {
        $query = "INSERT INTO tags (tag_name) VALUES (?)";
        $bind = [$tagName];
        $this->db->executeQuery($query, $bind);

        return $this->db->getLastInsertedId();
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Tag
    {
        $this->name = $name;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Tag
    {
        $this->id = $id;
        return $this;
    }
}