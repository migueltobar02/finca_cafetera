<?php
/**
 * Conexión sencilla a MySQL en Railway
 */

function conectarDB() {
    // Obtener variables de entorno de Railway
    $host = getenv('MYSQLHOST');
    $port = getenv('MYSQLPORT') ?: 3306;
    $db   = getenv('MYSQLDATABASE');
    $user = getenv('MYSQLUSER');
    $pass = getenv('MYSQLPASSWORD');
    
    try {
        // Crear conexión PDO
        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        return $pdo;
        
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// Uso:
// $pdo = conectarDB();
