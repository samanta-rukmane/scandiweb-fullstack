<?php

declare(strict_types=1);

namespace App\Models;

// Represents a customer order
class Order
{
    protected int $id;
    protected array $items;
    protected float $total;

    public function __construct(array $data)
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->items = $data['items'] ?? [];
        $this->total = (float) ($data['total'] ?? 0.0);
    }

    public function getId(): int { return $this->id; }
    public function getItems(): array { return $this->items; }
    public function getTotal(): float { return $this->total; }
}