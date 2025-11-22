<?php
require_once dirname(__DIR__) . '/helpers/EnvLoader.php';

// Cargar variables de entorno
EnvLoader::load();

class DatabaseConfig {

    public static function getConfig() {
        return [
            'host' => EnvLoader::get('MYSQLHOST', 'localhost'),   // tu host de Postgres
            'port' => EnvLoader::get('MYSQLPORT', 5432),          // puerto de Postgres
            'user' => EnvLoader::get('MYSQLUSER', 'postgres'),    // usuario de Postgres
            'password' => EnvLoader::get('MYSQLPASSWORD', ''),    // contraseña
            'database' => EnvLoader::get('MYSQLDATABASE', 'finca_cafetera'), // nombre BD
            'charset' => 'UTF8' // PostgreSQL no usa utf8mb4
        ];
    }
}
