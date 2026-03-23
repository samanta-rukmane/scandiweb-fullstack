<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL type for an order
 * Contains the order ID, list of items, and total price
 */
class OrderType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name'   => 'Order',
            'fields' => [
                'id' => Type::nonNull(Type::string()),                 // Unique order identifier
                'items' => Type::listOf(new OrderItemType()),          // List of items in the order
                'total' => Type::nonNull(Type::float()),              // Total price of the order
            ],
        ]);
    }
}