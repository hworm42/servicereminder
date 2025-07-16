<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private string $dbPath;

    private function __construct()
    {
        $this->dbPath = __DIR__ . '/../database/database.sqlite';
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $dsn = 'sqlite:' . $this->dbPath;
            self::$instance = new PDO($dsn);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Veritabanı bağlantı hatası: ' . $e->getMessage());
            die('Veritabanı bağlantı hatası.');
        }
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            new self();
        }
        return self::$instance;
    }

    public function createTables(): void
    {
        $sql = file_get_contents(__DIR__ . '/../database/init.sql');
        if ($sql === false) {
            error_log('init.sql dosyası okunamadı.');
            die('Veritabanı tabloları oluşturulamadı.');
        }

        try {
            self::$instance->exec($sql);
            error_log('Veritabanı tabloları başarıyla oluşturuldu veya zaten mevcut.');
        } catch (PDOException $e) {
            error_log('Veritabanı tabloları oluşturulurken hata: ' . $e->getMessage());
            die('Veritabanı tabloları oluşturulurken hata.');
        }
    }
}
