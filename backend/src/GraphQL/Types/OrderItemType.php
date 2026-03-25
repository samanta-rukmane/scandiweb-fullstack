<?php
declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class OrderItemType extends ObjectType
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct([
            'name'   => 'OrderItem',
            'fields' => [
                'product'    => ProductType::getInstance(),
                'quantity'   => Type::nonNull(Type::int()),
                'attributes' => Type::listOf(AttributeType::getInstance()),
            ],
        ]);
    }
}