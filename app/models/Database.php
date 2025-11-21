<?php
/**
 * Clase Singleton para manejar la conexión a la base de datos
 */

class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        // Cargar configuración
        require_once __DIR__ . '/../config/database.php';
        
        try {
            $config = DatabaseConfig::getConfig();
            $port = !empty($config['port']) && $config['port'] != 3306 ? ";port=" . $config['port'] : "";
            $dsn = "mysql:host=" . $config['host'] . $port . ";dbname=" . $config['database'] . ";charset=" . $config['charset'];
            $this->connection = new PDO($dsn, $config['user'], $config['password']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}
?>