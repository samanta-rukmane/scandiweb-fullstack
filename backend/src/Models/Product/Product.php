<?php

declare(strict_types=1);

namespace App\Models\Product;

// Represents a simple product without configurable attributes
class Product extends AbstractProduct
{
    // Return the type of the product
    public function getType(): string { return 'simple'; }
}