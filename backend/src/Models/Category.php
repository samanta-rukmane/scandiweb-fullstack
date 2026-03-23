<?php

declare(strict_types=1);

namespace App\Models;

// Represents a product category
class Category
{
    protected int $id;
    protected string $name;
    protected string $slug;

    public function __construct(array $data)
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->slug = $data['slug'] ?? strtolower(str_replace(' ', '-', $this->name));
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
}