<?php

namespace App\Models;

use App\Models\Tag;

class Facility
{
    private $id;
    private $name;
    private $creationDate;
    private $location;
    private $tags = [];

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
