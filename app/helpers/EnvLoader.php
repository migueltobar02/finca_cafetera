<?php
/**
 * Environment Variables Loader
 * Carga variables de un archivo .env en $_ENV y getenv()
 */
class EnvLoader {
    private static $loaded = false;
    private static $envPath = null;

    /**
     * Carga variables de entorno desde archivo .env
     * @param string|null $path Ruta al archivo .env (por defecto raíz del proyecto)
     * @return bool true si se cargó exitosamente, false si archivo no existe
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return true;
        }

        if ($path === null) {
            $path = dirname(dirname(dirname(__FILE__))) . '/.env';
        }

        if (!file_exists($path)) {
            return false;
        }

        self::$envPath = $path;
        self::parseEnvFile($path);
        self::$loaded = true;

        return true;
    }

    /**
     * Parsea el archivo .env y carga variables
     * @param string $path Ruta al archivo .env
     */
    private static function parseEnvFile($path) {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos($line, '#') === 0) {
                continue;
            }

            // Buscar el signo = para separar clave y valor
            if (strpos($line, '=') === false) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remover comillas si existen
            if (preg_match('/^"(.+)"$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match("/^'(.+)'$/", $value, $matches)) {
                $value = $matches[1];
            }

            // Evitar sobrescribir variables ya definidas
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    /**
     * Obtiene una variable de entorno
     * @param string $key Nombre de la variable
     * @param mixed $default Valor por defecto si no existe
     * @return mixed Valor de la variable o default
     */
    public static function get($key, $default = null) {
        self::load(); // Asegurar que está cargado

        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        $value = getenv($key);
        return $value !== false ? $value : $default;
    }

    /**
     * Verifica si una variable de entorno existe
     * @param string $key Nombre de la variable
     * @return bool true si existe
     */
    public static function has($key) {
        self::load(); // Asegurar que está cargado
        return isset($_ENV[$key]) || getenv($key) !== false;
    }

    /**
     * Establece una variable de entorno (en tiempo de ejecución)
     * @param string $key Nombre de la variable
     * @param string $value Valor de la variable
     */
    public static function set($key, $value) {
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}
?>
