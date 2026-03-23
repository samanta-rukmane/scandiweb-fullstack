<?php
declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use App\GraphQL\Types\AttributeType;
use App\GraphQL\Types\ProductType;

/**
 * GraphQL type for a single item in an order
 * Includes the product, quantity, and selected attributes
 */
class OrderItemType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name'   => 'OrderItem',
            'fields' => [
                'product' => ProductType::getInstance(),              // Product object
                'quantity' => Type::nonNull(Type::int()),           // Quantity ordered
                'attributes' => Type::listOf(AttributeType::getInstance()), // Selected product attributes
            ],
        ]);
    }
}