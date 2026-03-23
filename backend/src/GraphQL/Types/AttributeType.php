<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL type for product attributes
 * Example: Color, Size, Capacity
 */
class AttributeType extends ObjectType
{
    private static ?self $instance = null;

    // Get singleton instance of AttributeType
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $attributeItemType = new AttributeItemType();

        parent::__construct([
            'name'   => 'Attribute',
            'fields' => [
                'name' => Type::nonNull(Type::string()), // Attribute name (e.g., Color)
                'type' => Type::nonNull(Type::string()), // Attribute type (swatch or text)
                'items' => [
                    'type'    => Type::listOf($attributeItemType), // List of possible values
                    'resolve' => fn($attr) => $attr->getItems() ?? [],
                ],
            ],
        ]);
    }
}