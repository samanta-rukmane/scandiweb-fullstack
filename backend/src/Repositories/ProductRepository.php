<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Product\AbstractProduct;
use App\Models\Product\ProductFactory;

// Repository for managing products
class ProductRepository
{
    private AttributeRepository $attributeRepo;

    public function __construct()
    {
        $this->attributeRepo = new AttributeRepository();
    }

    /**
     * Get all products
     *
     * @return AbstractProduct[]
     */
    public function findAll(): array
    {
        $db = Database::getConnection();
        $rows = $db->query("SELECT * FROM products")->fetchAll(\PDO::FETCH_ASSOC);

        return array_map([$this, 'mapToProduct'], $rows);
    }

    /**
     * Find a product by its ID
     *
     * @param string $id
     * @return AbstractProduct|null
     */
    public function findById(string $id): ?AbstractProduct
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ? $this->mapToProduct($row) : null;
    }

    /**
     * Find products by category name
     *
     * @param string $categoryName
     * @return AbstractProduct[]
     */
    public function findByCategory(string $categoryName): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT id FROM categories WHERE LOWER(name) = :name LIMIT 1");
        $stmt->execute(['name' => strtolower($categoryName)]);
        $category = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$category) {
            return [];
        }

        $stmt = $db->prepare("SELECT * FROM products WHERE category_id = :cid");
        $stmt->execute(['cid' => $category['id']]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map([$this, 'mapToProduct'], $rows);
    }

    /**
     * Map a raw DB row to a product instance
     *
     * @param array $row
     * @return AbstractProduct
     */
    private function mapToProduct(array $row): AbstractProduct
    {
        $db = Database::getConnection();

        // Fetch all prices (with currency info)
        $stmt = $db->prepare("SELECT amount, currency_label, currency_symbol FROM prices WHERE product_id = :pid");
        $stmt->execute(['pid' => $row['id']]);
        $priceRows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $prices = array_map(fn($p) => [
            'amount'   => (float) $p['amount'],
            'currency' => [
                'label'  => $p['currency_label'],
                'symbol' => $p['currency_symbol'],
            ],
        ], $priceRows);

        // Fetch gallery images
        $stmt = $db->prepare("SELECT image_url FROM product_galleries WHERE product_id = :pid");
        $stmt->execute(['pid' => $row['id']]);
        $galleryRows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $gallery = array_map(fn(array $r) => $r['image_url'] ?? '', $galleryRows);

        // Fetch attributes
        $rawAttributes = $this->attributeRepo->findByProductId((string) $row['id']) ?? [];
        $attributes = array_filter(
            $rawAttributes,
            fn($attr) => $attr instanceof \App\Models\Attribute\AbstractAttribute
        );

        $data = [
            'id'          => (string) ($row['id'] ?? ''),
            'name'        => $row['name'] ?? '',
            'brand'       => $row['brand'] ?? '',
            'prices'      => $prices,
            'inStock'     => (bool) ($row['in_stock'] ?? false),
            'gallery'     => $gallery,
            'attributes'  => $attributes,
            'categoryId'  => (string) ($row['category_id'] ?? ''),
            'description' => $row['description'] ?? '',
            'type'        => $row['type'] ?? 'simple',
        ];

        return ProductFactory::create($data);
    }
}