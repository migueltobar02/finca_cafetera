<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Incluir autoload
require_once __DIR__ . '/app/autoload.php';

// Redirigir según sesión
if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
