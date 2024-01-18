<?php

namespace App\Models;

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

    public function setCreationDate(): Facility
    {
        $this->creationDate = date("Y-m-d H:i:s");
        return $this;
    }

    public function getCreationDate(): string
    {
        return $this->creationDate;
    }
}
