<?php
// Habilitar errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Usar ruta absoluta
require_once __DIR__ . '/../app/autoload.php';

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirección
if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
?>