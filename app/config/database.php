<?php
/**
 * Conexión a MySQL en Railway usando PDO
 * Funciona directamente con las variables de entorno de Railway
 */

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            // Obtener los datos de conexión desde Railway
            $host = getenv('MYSQLHOST');
            $port = getenv('MYSQLPORT') ?: 3306;
            $user = getenv('MYSQLUSER');
            $password = getenv('MYSQLPASSWORD');
            $database = getenv('MYSQLDATABASE');
            $charset = 'utf8mb4';

            if (!$host || !$user || !$database) {
                die("Error: faltan variables de entorno necesarias en Railway.");
            }

            try {
                self::$pdo = new PDO(
                    "mysql:host={$host};port={$port};dbname={$database};charset={$charset}",
                    $user,
                    $password
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Error al conectar a la base de datos: " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}

// Ejemplo de uso
$pdo = Database::getConnection();

// Ejemplo de prueba: obtener la cantidad de usuarios
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM usuarios");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Usuarios en la base de datos: " . $row['total'];
