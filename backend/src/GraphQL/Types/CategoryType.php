<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use App\Models\Category;

/**
 * GraphQL type for a product category
 * Example: clothes, tech, all
 */
class CategoryType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name'        => 'Category',
            'description' => 'Represents a product category',
            'fields'      => [
                'id' => [
                    'type'        => Type::nonNull(Type::int()), // Unique ID
                    'description' => 'Unique identifier of the category',
                    'resolve'     => fn($category) => $category instanceof Category ? $category->getId() : null,
                ],
                'name' => [
                    'type'        => Type::nonNull(Type::string()), // Category name
                    'description' => 'Category name',
                    'resolve'     => fn($category) => $category instanceof Category ? $category->getName() : null,
                ],
                'slug' => [
                    'type'        => Type::nonNull(Type::string()), // URL-friendly string
                    'description' => 'Category slug for URLs',
                    'resolve'     => fn($category) => $category instanceof Category ? $category->getSlug() : null,
                ],
            ],
        ]);
    }
}