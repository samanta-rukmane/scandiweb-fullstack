<?php

namespace App\Models\Attribute;

/**
 * Abstract class for a product attribute
 * All attributes (text, swatch, etc.) should extend this class
 */
abstract class AbstractAttribute
{
    protected string $id;
    protected string $name;
    protected string $type;
    protected array $items;

    // Constructor accepts an array of data
    public function __construct(array $data)
    {
        $this->id    = $data['id'] ?? '';
        $this->name  = $data['name'] ?? '';
        $this->type = $data['type'] ?? $this->getType();
        $this->items = $data['items'] ?? [];
    }

    // Returns the attribute type (implemented by child classes)
    abstract public function getType(): string;

    // Getters
    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getTypeName(): string { return $this->type; }
    public function getItems(): array { return $this->items; }

    // Convert attribute to array for GraphQL or serialization
    public function toArray(): array
    {
        $items = array_map(fn($item) => [
            'value'        => $item['value'] ?? null,
            'displayValue' => $item['display_value'] ?? $item['value'] ?? null,
        ], $this->items);

        return [
            'name'  => $this->name,
            'type'  => $this->type,
            'items' => $items,
        ];
    }
}