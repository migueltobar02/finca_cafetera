<?php
/**
 * Prueba de conexión a MySQL en Railway
 * Funciona solo dentro de Railway
 */

// Obtener datos de la base de datos desde variables de entorno
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT') ?: 3306;
$db   = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

if (!$host || !$db || !$user) {
    die("Faltan variables de entorno necesarias para conectarse a la base de datos.");
}

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Conexión exitosa a la base de datos Railway!<br>";

    // Consulta de prueba: contar usuarios
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM usuarios");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Usuarios en la base de datos: " . $row['total'];

} catch (PDOException $e) {
    die("❌ Error al conectar a la base de datos: " . $e->getMessage());
}
