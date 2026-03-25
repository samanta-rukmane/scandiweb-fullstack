<?php
declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\Order;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class OrderType extends ObjectType
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
            'name'   => 'Order',
            'fields' => [
                'id' => [
                    'type'    => Type::nonNull(Type::int()),
                    'resolve' => fn($order) => $order instanceof Order ? $order->getId() : null,
                ],
                'total' => [
                    'type'    => Type::nonNull(Type::float()),
                    'resolve' => fn($order) => $order instanceof Order ? $order->getTotal() : 0,
                ],
                'items' => [
                    'type'    => Type::listOf(OrderItemType::getInstance()),
                    'resolve' => fn($order) => $order instanceof Order ? $order->getItems() : [],
                ],
            ],
        ]);
    }
}