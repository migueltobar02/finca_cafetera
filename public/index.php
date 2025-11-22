<?php
// Habilitar errores para debug (puedes desactivarlo en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Usar ruta absoluta correcta dentro del contenedor
require_once __DIR__ . '/app/autoload.php';

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirección a las páginas correctas dentro de 'public/'
// Asegúrate que 'login.php' y 'dashboard.php' estén en la misma carpeta que index.php
if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php'); // si dashboard.php está en public/
    exit;
} else {
    header('Location: login.php'); // si login.php está en public/
    exit;
}

