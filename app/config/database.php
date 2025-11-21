<?php
require_once dirname(__DIR__) . '/helpers/EnvLoader.php';

// Cargar variables de entorno
EnvLoader::load();

class DatabaseConfig {
    const HOST = MYSQLHOST;
    const PORT = MYSQLPORT ?? 3306;
    const USERNAME = MYSQLUSER;
    const PASSWORD = MYSQLPASSWORD;
    const DATABASE = MYSQLDATABASE;
    const CHARSET = 'utf8mb4';

    /**
     * Obtiene la configuración de base de datos desde variables de entorno
     * @return array Configuración con valores actuales
     */
    public static function getConfig() {
        return [
            'host' => EnvLoader::get('MYSQLHOST', 'localhost'),
            'port' => EnvLoader::get('MYSQLPORT', 3306),
            'user' => EnvLoader::get('MYSQLUSER', 'root'),
            'password' => EnvLoader::get('MYSQLPASSWORD', ''),
            'database' => EnvLoader::get('MYSQLDATABASE', 'finca_cafetera'),
            'charset' => 'utf8mb4'
        ];
    }
}
?>