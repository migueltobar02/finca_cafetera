<?php
/**
 * database.php
 * Archivo de configuración y prueba de conexión a MySQL en Railway
 */

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            // Leer datos de entorno de Railway
            $host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
            $port = getenv('MYSQLPORT') ?: 3306;
            $db   = getenv('MYSQLDATABASE') ?: 'railway';
            $user = getenv('MYSQLUSER') ?: 'root';
            $pass = getenv('MYSQLPASSWORD') ?: '';

            try {
                self::$pdo = new PDO(
                    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
                    $user,
                    $pass
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("❌ Error al conectar a la base de datos: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}

// ------------------------
// Bloque de prueba (opcional)
// Solo se ejecuta si accedes directamente al archivo
// ------------------------
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $pdo = Database::getConnection();
    echo "✅ Conexión exitosa a la base de datos Railway!<br>";

    // Ejemplo de prueba: contar usuarios
    try {
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM usuarios");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Usuarios en la base de datos: " . $row['total'];
    } catch (PDOException $e) {
        echo "Error al ejecutar consulta de prueba: " . $e->getMessage();
    }
}
