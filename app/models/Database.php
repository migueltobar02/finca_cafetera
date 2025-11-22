<?php
require_once __DIR__ . '/../config/database.php';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $config = DatabaseConfig::getConfig();

        // DSN para PostgreSQL
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']};options='--client_encoding={$config['charset']}'";

        try {
            $this->pdo = new PDO(
                $dsn,
                $config['user'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}
