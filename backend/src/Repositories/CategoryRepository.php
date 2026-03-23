<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Category;

// Repository for managing Category entities
class CategoryRepository
{
    /**
     * Get all categories
     *
     * @return Category[]
     */
    public function findAll(): array
    {
        $db = Database::getConnection();

        $rows = $db->query("SELECT * FROM categories")
                   ->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => new Category($row), $rows);
    }

    /**
     * Find a category by its slug
     *
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category
    {
        $db = Database::getConnection();

        $rows = $db->query("SELECT * FROM categories")->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $category = new Category($row);
            if ($category->getSlug() === $slug) {
                return $category;
            }
        }

        return null;
    }
}