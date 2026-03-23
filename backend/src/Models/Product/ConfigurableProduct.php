<?php

namespace App\Models\Product;

/**
 * Represents a configurable product with selectable attributes
 * (e.g., color, size, capacity)
 */
class ConfigurableProduct extends AbstractProduct
{
    // Return the type of the product
    public function getType(): string { return 'configurable'; }
}