<?php
// Conexión simple a MySQL en Railway

$host = 'mysql.railway.internal';
$port = 3306;
$db   = 'railway';
$user = 'root';
$pass = 'bLtyAOVlShxcPDCDHhUvDTVpDxwVSkbA';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexión exitosa a la base de datos!";
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}

// Ejemplo de consulta
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM usuarios");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<br>Usuarios en la base de datos: " . $row['total'];
