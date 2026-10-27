<?php

namespace App\Config;

use PDO;
use PDOException;
use RuntimeException;
use Dotenv\Dotenv;

class Database
{
    private static ?PDO $connection = null;

    private static function env(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);
        return ($value === false || $value === null || $value === '') ? $default : $value;
    }

    public static function getConnection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();

        try {
            $host     = self::env('DB_HOST', 'localhost');
            $port     = self::env('DB_PORT', '3306');
            $dbName   = self::env('DB_NAME');
            $username = self::env('DB_USER');
            $password = self::env('DB_PASS', '');

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            if (self::env('DB_SSL') === 'true') {
                $options[PDO::MYSQL_ATTR_SSL_CA] =
                    self::env('DB_SSL_CA', '/etc/ssl/certs/ca-certificates.crt');
            }

            self::$connection = new PDO(
                "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4",
                $username,
                $password,
                $options
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage());
        }

        return self::$connection;
    }
}