<?php
/**
 * Autoloader para cargar clases automáticamente
 */

spl_autoload_register(function ($className) {
    // Convertir namespace a ruta de archivo
    $classPath = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';
    
    if (file_exists($classPath)) {
        require_once $classPath;
        return true;
    }
    
    // Buscar en subdirectorios comunes
    $directories = ['models', 'controllers', 'config'];
    foreach ($directories as $dir) {
        $classPath = __DIR__ . '/' . $dir . '/' . $className . '.php';
        if (file_exists($classPath)) {
            require_once $classPath;
            return true;
        }
    }
    
    return false;
});

// Incluir manualmente las clases esenciales si es necesario
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/SecurityManager.php';
// Inicializar políticas de cookies y cabeceras de seguridad lo antes posible
try {
    // Configurar parámetros de cookies de sesión antes de iniciar
    if (class_exists('SecurityManager')) {
        SecurityManager::configureSessionCookies();
        SecurityManager::initSession();
        SecurityManager::setSecurityHeaders();
    }
} catch (Throwable $e) {
    // No bloquear la carga si algo falla; el entorno hospedado deberá revisar logs
}
?>