<?php

declare(strict_types=1);

namespace App\Models\Attribute;

// Swatch attribute (e.g. color)
class SwatchAttribute extends AbstractAttribute
{
    public function getType(): string { return 'swatch'; }
}