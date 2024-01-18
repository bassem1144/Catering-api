<?php

namespace App\Models;

class Facility
{
    private $id;
    private $name;
    private $creationDate;
    private $location;
    private $tags = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Facility
    {
        $this->name = $name;
        return $this;
    }
}
