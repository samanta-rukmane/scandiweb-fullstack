<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Attribute\AbstractAttribute;
use App\Models\Attribute\AttributeFactory;

class AttributeRepository
{
    /**
     * Fetch all attributes for a given product
     *
     * @return AbstractAttribute[]
     */
    public function findByProductId(string $productId): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT * FROM attributes WHERE product_id = :pid'
        );
        $stmt->execute(['pid' => $productId]);

        $attributes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];

        foreach ($attributes as $attr) {
            // Fetch attribute items
            $stmtItems = $db->prepare(
                'SELECT value, display_value FROM attribute_items WHERE attribute_id = :aid'
            );
            $stmtItems->execute(['aid' => $attr['id']]);

            $attr['items'] = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);

            // Map to domain model
            $result[] = AttributeFactory::create($attr);
        }

        return $result;
    }
}