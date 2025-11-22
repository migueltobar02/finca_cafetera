<?php
require_once dirname(__DIR__) . '/helpers/EnvLoader.php';

// Cargar variables de entorno
EnvLoader::load();

class DatabaseConfig {
    const HOST = 'PGHOST';  // ajusta si quieres usar constante
    const PORT = 'PGPORT' ?? 5432;
    const USERNAME = 'PGUSER';
    const PASSWORD = 'PGPASSWORD';
    const DATABASE = 'PGDATABASE';
    const CHARSET = 'UTF8'; // PostgreSQL no usa utf8mb4

    public static function getConfig() {
        return [
            'host' => EnvLoader::get('PGHOST', 'localhost'),
            'port' => EnvLoader::get('PGPORT', 5432),
            'user' => EnvLoader::get('PGUSER', 'postgres'),
            'password' => EnvLoader::get('PGPASSWORD', ''),
            'database' => EnvLoader::get('PGDATABASE', 'finca_cafetera'),
            'charset' => 'UTF8'
        ];
    }
}
