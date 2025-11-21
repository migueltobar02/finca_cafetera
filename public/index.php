<?php
/**
 * Punto de entrada principal del sistema
 * Redirige al dashboard si está autenticado, sino al login
 */

// Cargar autoloader primero
require_once __DIR__ . '/../app/autoload.php';

// Ahora cargar el controlador de autenticación
$auth = new AuthController();

// Verificar si ya está autenticado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
?>