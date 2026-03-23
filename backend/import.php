<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$pdo = new PDO(
    'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- Create tables ---
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

$pdo->exec("CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    in_stock TINYINT(1) DEFAULT 1,
    description TEXT,
    brand VARCHAR(255),
    type VARCHAR(50) DEFAULT 'simple',
    category_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(id)
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS product_galleries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(255),
    image_url TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id)
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(255),
    currency_label VARCHAR(50),
    currency_symbol VARCHAR(10),
    amount DECIMAL(10,2),
    FOREIGN KEY (product_id) REFERENCES products(id)
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(255),
    name VARCHAR(255),
    type VARCHAR(50),
    FOREIGN KEY (product_id) REFERENCES products(id)
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS attribute_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attribute_id INT,
    display_value VARCHAR(255),
    value VARCHAR(255),
    FOREIGN KEY (attribute_id) REFERENCES attributes(id)
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id VARCHAR(255),
    quantity INT,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS order_item_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_item_id INT,
    attribute_name VARCHAR(255),
    attribute_value VARCHAR(255),
    FOREIGN KEY (order_item_id) REFERENCES order_items(id)
)");

// --- Clear existing data ---
$pdo->exec("TRUNCATE TABLE order_item_attributes");
$pdo->exec("TRUNCATE TABLE order_items");
$pdo->exec("TRUNCATE TABLE orders");
$pdo->exec("TRUNCATE TABLE attribute_items");
$pdo->exec("TRUNCATE TABLE attributes");
$pdo->exec("TRUNCATE TABLE prices");
$pdo->exec("TRUNCATE TABLE product_galleries");
$pdo->exec("TRUNCATE TABLE products");
$pdo->exec("TRUNCATE TABLE categories");

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

$json = json_decode(file_get_contents('data.json'), true);
$data = $json['data'];

$categories_inserted = [];
$attributes_inserted = [];
$products_inserted = [];

// --- Categories ---
foreach ($data['categories'] as $category) {
    $name = $category['name'];

    if (!isset($categories_inserted[$name])) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        $categories_inserted[$name] = $pdo->lastInsertId();
    }
}

// --- Products ---
foreach ($data['products'] as $product) {
    if (isset($products_inserted[$product['id']])) continue;

    $category_name = $product['category'] ?? 'all';
    $category_id = $categories_inserted[$category_name] ?? null;

    $in_stock = isset($product['inStock']) ? (int)$product['inStock'] : 1;
    $description = $product['description'] ?? '';
    $brand = $product['brand'] ?? '';

    $type = !empty($product['attributes']) ? 'configurable' : 'simple';

    $stmt = $pdo->prepare("
        INSERT INTO products (id, name, in_stock, description, brand, type, category_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $product['id'],
        $product['name'],
        $in_stock,
        $description,
        $brand,
        $type,
        $category_id
    ]);

    $products_inserted[$product['id']] = true;

    foreach ($product['gallery'] ?? [] as $img) {
        $stmt = $pdo->prepare("INSERT INTO product_galleries (product_id, image_url) VALUES (?, ?)");
        $stmt->execute([$product['id'], $img]);
    }

    foreach ($product['prices'] ?? [] as $price) {
        $currency_label = $price['currency']['label'] ?? 'USD';
        $currency_symbol = $price['currency']['symbol'] ?? '$';
        $amount = $price['amount'] ?? 0;

        $stmt = $pdo->prepare("
            INSERT INTO prices (product_id, currency_label, currency_symbol, amount)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$product['id'], $currency_label, $currency_symbol, $amount]);
    }

    foreach ($product['attributes'] ?? [] as $attrSet) {
        $attr_name = $attrSet['name'] ?? null;
        $attr_type = $attrSet['type'] ?? 'text';

        if (!$attr_name) continue;

        if (!isset($attributes_inserted[$product['id']][$attr_name])) {
            $stmt = $pdo->prepare("INSERT INTO attributes (product_id, name, type) VALUES (?, ?, ?)");
            $stmt->execute([$product['id'], $attr_name, $attr_type]);
            $attr_id = $pdo->lastInsertId();
            $attributes_inserted[$product['id']][$attr_name] = $attr_id;
        } else {
            $attr_id = $attributes_inserted[$product['id']][$attr_name];
        }

        foreach ($attrSet['items'] ?? [] as $item) {
            $display_value = $item['displayValue'] ?? '';
            $value = $item['value'] ?? '';

            $stmt = $pdo->prepare("
                INSERT INTO attribute_items (attribute_id, display_value, value)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$attr_id, $display_value, $value]);
        }
    }
}

echo "Import complete\n";