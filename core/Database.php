<?php

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    // private string $host = 'localhost';
    // private string $dbName = 'user_system';
    // private string $username = 'root';
    // private string $password = '';
    // private string $charset = 'utf8mb4';

    // live database connection details
    private string $host = 'mobilequipmentkcml.com';
    private string $dbName = 'u704412686_equipment';
    private string $username = 'u704412686_equipment';
    private string $password = '5/rnD6OoGE*Q';
    private string $charset = 'utf8mb4';

    // Constructor خاص حتى نمنع إنشاء نسخ متعددة
    private function __construct()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // دالة للحصول على نفس النسخة دائماً (Singleton)
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    // دالة لجلب الاتصال (PDO)
    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
