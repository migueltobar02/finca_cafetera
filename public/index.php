<?php
// Habilitar errores para debug (opcional)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir autoload desde app
require_once __DIR__ . '/app/autoload.php';

// Redirigir según sesión
if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
