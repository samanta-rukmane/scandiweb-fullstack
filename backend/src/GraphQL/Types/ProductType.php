<?php
declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\Product\AbstractProduct;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use App\GraphQL\Types\AttributeType;

class ProductType extends ObjectType
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct([
            'name' => 'Product',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'resolve' => fn($product) =>
                        $product instanceof AbstractProduct ? $product->getId() : null,
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => fn($product) =>
                        $product instanceof AbstractProduct ? $product->getName() : null,
                ],
                'brand' => [
                    'type' => Type::string(),
                    'resolve' => fn($product) =>
                        $product instanceof AbstractProduct ? $product->getBrand() : null,
                ],
                'description' => [
                    'type' => Type::string(),
                    'resolve' => fn($product) =>
                        $product instanceof AbstractProduct ? $product->getDescription() : '',
                ],
                'prices' => [
                    'type' => Type::listOf(new ObjectType([
                        'name' => 'Price',
                        'fields' => [
                            'amount' => Type::nonNull(Type::float()),
                            'currency' => new ObjectType([
                                'name' => 'Currency',
                                'fields' => [
                                    'label'  => Type::nonNull(Type::string()),
                                    'symbol' => Type::nonNull(Type::string()),
                                ],
                            ]),
                        ],
                    ])),
                    'resolve' => fn($product) =>
                        $product instanceof AbstractProduct ? $product->getPrices() : [],
                ],
                'gallery' => [
                    'type' => Type::listOf(Type::string()),
                    'resolve' => fn($product) =>
                        $product instanceof AbstractProduct ? $product->getGallery() : [],
                ],
                'inStock' => [
                    'type' => Type::boolean(),
                    'resolve' => fn($product) =>
                        $product instanceof AbstractProduct ? $product->isInStock() : false,
                ],
                'attributes' => [
                    'type' => Type::listOf(AttributeType::getInstance()),
                    'resolve' => fn($product) => array_map(
                        fn($attr) => $attr->toArray(),
                        $product->getAttributes() ?? []
                    ),
                ],
            ],
        ]);
    }
}