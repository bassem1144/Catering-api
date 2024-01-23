<?php

namespace App\Models;

class Tag
{
    private $id;
    private $name;

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