<?php

declare(strict_types=1);

namespace App\Models\Product;

// Simple product without configurable attributes
class SimpleProduct extends AbstractProduct
{
    public function getType(): string { return 'simple'; }
}