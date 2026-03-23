<?php
declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\Product\AbstractProduct;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL type for a Product
 * Contains id, name, brand, description, price, gallery, stock status, and attributes
 */
class ProductType extends ObjectType
{
    private static ?self $instance = null;

    // Get singleton instance of ProductType
    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        // Define nested ObjectType for attribute items
        $attributeItemType = new ObjectType([
            'name' => 'AttributeItem',
            'fields' => [
                'value' => Type::string(),
                'displayValue' => Type::string(),
            ],
        ]);

        // Define nested ObjectType for attributes
        $attributeType = new ObjectType([
            'name' => 'Attribute',
            'fields' => [
                'name' => Type::nonNull(Type::string()),
                'type' => Type::nonNull(Type::string()),
                'items' => Type::listOf($attributeItemType),
            ],
        ]);

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
                    'type' => Type::listOf($attributeType),
                    'resolve' => function(AbstractProduct $product) {
                        // Map attributes to array using toArray() method
                        $attrs = $product->getAttributes() ?? [];
                        return array_map(fn($attr) => $attr->toArray(), $attrs);
                    },
                ],
            ],
        ]);
    }
}