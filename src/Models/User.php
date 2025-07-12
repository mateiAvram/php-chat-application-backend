<?php

declare(strict_types=1);

namespace App\Models;

use JsonSerializable;

class User implements JsonSerializable
{
    private int $id;
    private string $name;

    // Constructors
    public function __construct(string $name, ?int $id = null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }

    // Setters
    public function setName($name): void
    {
        $this->name = $name;
    }

    // JSON serializable
    public function jsonSerialize(): array
    {
        return [
            'id'        => $this->id,
            'name'   => $this->name
        ];
    }
}
