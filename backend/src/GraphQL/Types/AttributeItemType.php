<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL type for a single product attribute item
 * Example: a color or size option
 */
class AttributeItemType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name'   => 'AttributeItem',
            'fields' => [
                'value'        => Type::string(), // Raw value (e.g., #FFFFFF or 'L')
                'displayValue' => Type::string(), // Human-readable value (optional)
            ],
        ]);
    }
}