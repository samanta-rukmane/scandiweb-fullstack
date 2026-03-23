<?php

declare(strict_types=1);

namespace App\Models\Attribute;

use InvalidArgumentException;

class AttributeFactory
{
    // Create attribute instance based on type
    public static function create(array $data): AbstractAttribute
    {
        return match ($data['type']) {
            'text' => new TextAttribute($data),
            'swatch' => new SwatchAttribute($data),
            default => throw new InvalidArgumentException('Unknown attribute type'),
        };
    }
}