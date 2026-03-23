<?php

namespace App\Models\Product;

/**
 * Factory class for creating product instances
 * Decides between SimpleProduct and ConfigurableProduct
 */
class ProductFactory
{
    /**
     * Create a product instance based on type
     *
     * @param array $data Product data including 'type'
     * @return AbstractProduct
     * @throws \Exception if product type is unknown
     */
    public static function create(array $data): AbstractProduct
    {
        return match ($data['type'] ?? 'simple') {
            'simple' => new SimpleProduct($data),
            'configurable' => new ConfigurableProduct($data),
            default => throw new \Exception('Unknown product type: ' . ($data['type'] ?? 'none')),
        };
    }
}