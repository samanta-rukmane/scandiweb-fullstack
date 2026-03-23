<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Order;
use App\Repositories\ProductRepository;

// Repository for handling customer orders
class OrderRepository
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Create a new order with items and attributes
     *
     * @param array $items Array of items, each containing productId, quantity, attributes
     * @param float $total Total order amount
     * @return Order
     * @throws \RuntimeException on failure
     */
    public function create(array $items, float $total): Order
    {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            // Insert order
            $stmt = $db->prepare("INSERT INTO orders (total) VALUES (:total)");
            $stmt->execute(['total' => $total]);
            $orderId = (int) $db->lastInsertId();

            // Prepare statement for order items
            $stmtItem = $db->prepare("
                INSERT INTO order_items (order_id, product_id, quantity)
                VALUES (:order_id, :product_id, :quantity)
            ");

            $finalItems = [];

            foreach ($items as $item) {
                $productId = (string) ($item['productId'] ?? '');
                $product = $this->productRepository->findById($productId);

                if (!$product) {
                    throw new \RuntimeException("Product NOT FOUND: " . $productId);
                }

                // Insert order item
                $stmtItem->execute([
                    'order_id' => $orderId,
                    'product_id' => $product->getId(),
                    'quantity' => $item['quantity'] ?? 1,
                ]);

                $orderItemId = (int) $db->lastInsertId();

                // Insert item attributes
                $itemAttributes = $item['attributes'] ?? [];
                $formattedAttributes = [];

                foreach ($itemAttributes as $attr) {
                    if (!isset($attr['name'], $attr['value'])) {
                        continue;
                    }

                    $stmtAttr = $db->prepare("
                        INSERT INTO order_item_attributes (order_item_id, attribute_name, attribute_value)
                        VALUES (:order_item_id, :attribute_name, :attribute_value)
                    ");
                    $stmtAttr->execute([
                        'order_item_id' => $orderItemId,
                        'attribute_name' => $attr['name'],
                        'attribute_value' => $attr['value'],
                    ]);

                    $formattedAttributes[] = [
                        'name' => $attr['name'],
                        'type' => null,
                        'items' => [
                            [
                                'value' => $attr['value'],
                                'displayValue' => $attr['value']
                            ]
                        ]
                    ];
                }

                $finalItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'] ?? 1,
                    'attributes' => $formattedAttributes
                ];
            }

            $db->commit();

            return new Order([
                'id' => $orderId,
                'items' => $finalItems,
                'total' => $total,
            ]);

        } catch (\Exception $e) {
            $db->rollBack();

            error_log("ORDER ERROR: " . $e->getMessage());
            error_log($e->getTraceAsString());

            throw new \RuntimeException($e->getMessage());
        }
    }
}