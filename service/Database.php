<?php

namespace App;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            // Load .env file
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();

            $dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/../database.sqlite';
            try {
                $dsn = "sqlite:" . $dbPath;
                self::$instance = new PDO($dsn);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Veritabanı bağlantı hatası: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public function prepare(string $sql): \PDOStatement
    {
        return self::getInstance()->prepare($sql);
    }

    public function query(string $sql): \PDOStatement
    {
        return self::getInstance()->query($sql);
    }

    public function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }
}
