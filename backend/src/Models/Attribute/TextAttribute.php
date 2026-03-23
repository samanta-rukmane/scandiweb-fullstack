<?php

declare(strict_types=1);

namespace App\Models\Attribute;

// Text attribute (e.g., size, capacity)
class TextAttribute extends AbstractAttribute
{
    public function getType(): string { return 'text'; }
}