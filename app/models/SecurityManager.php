<?php
/**
 * Gestor de Seguridad - CSRF y XSS Protection
 */

class SecurityManager {
    private const CSRF_TOKEN_NAME = '_csrf_token';
    private const CSRF_TOKEN_TIME = '_csrf_token_time';
    private const CSRF_TIMEOUT = 3600; // 1 hora en segundos
    private const TOKEN_LENGTH = 32;

    /**
     * Inicializa la sesión si no está iniciada
     */
    public static function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Genera un token CSRF
     */
    public static function generateCSRFToken() {
        self::initSession();

        // Verificar si el token existe y no ha expirado
        if (isset($_SESSION[self::CSRF_TOKEN_NAME]) && isset($_SESSION[self::CSRF_TOKEN_TIME])) {
            if (time() - $_SESSION[self::CSRF_TOKEN_TIME] < self::CSRF_TIMEOUT) {
                return $_SESSION[self::CSRF_TOKEN_NAME];
            }
        }

        // Generar nuevo token
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::CSRF_TOKEN_NAME] = $token;
        $_SESSION[self::CSRF_TOKEN_TIME] = time();

        return $token;
    }

    /**
     * Obtiene el token CSRF actual
     */
    public static function getCSRFToken() {
        self::initSession();
        return $_SESSION[self::CSRF_TOKEN_NAME] ?? self::generateCSRFToken();
    }

    /**
     * Valida un token CSRF
     */
    public static function validateCSRFToken($token) {
        self::initSession();

        // Verificar que el token existe
        if (!isset($_SESSION[self::CSRF_TOKEN_NAME])) {
            return false;
        }

        // Verificar que no ha expirado
        if (!isset($_SESSION[self::CSRF_TOKEN_TIME])) {
            return false;
        }

        if (time() - $_SESSION[self::CSRF_TOKEN_TIME] > self::CSRF_TIMEOUT) {
            unset($_SESSION[self::CSRF_TOKEN_NAME]);
            unset($_SESSION[self::CSRF_TOKEN_TIME]);
            return false;
        }

        // Usar hash_equals para prevenir timing attacks
        if (!hash_equals($_SESSION[self::CSRF_TOKEN_NAME], $token)) {
            return false;
        }

        return true;
    }

    /**
     * Valida token CSRF desde POST o GET
     */
    public static function validateCSRFFromRequest() {
        $token = $_POST[self::CSRF_TOKEN_NAME] ?? $_GET[self::CSRF_TOKEN_NAME] ?? null;

        if (!$token) {
            return false;
        }

        return self::validateCSRFToken($token);
    }

    /**
     * Regenera el token CSRF después de validación
     */
    public static function regenerateCSRFToken() {
        self::initSession();
        unset($_SESSION[self::CSRF_TOKEN_NAME]);
        unset($_SESSION[self::CSRF_TOKEN_TIME]);
        return self::generateCSRFToken();
    }

    /**
     * Sanitiza entrada HTML - Previene XSS
     */
    public static function sanitizeInput($input, $type = 'text') {
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return self::sanitizeInput($item, $type);
            }, $input);
        }

        // Trim de espacios
        $input = trim($input);

        // Remover caracteres nulos
        $input = str_replace("\0", "", $input);

        switch ($type) {
            case 'text':
                // Escaper básico
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

            case 'email':
                // Validar email
                if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
                    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
                }
                return '';

            case 'url':
                // Validar URL
                if (filter_var($input, FILTER_VALIDATE_URL)) {
                    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
                }
                return '';

            case 'int':
                // Solo números
                return (int) filter_var($input, FILTER_VALIDATE_INT);

            case 'float':
                // Solo números decimales
                return (float) filter_var($input, FILTER_VALIDATE_FLOAT);

            case 'html':
                // Permitir HTML limitado (requiere whitelist)
                return self::sanitizeHTML($input);

            default:
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Sanitiza HTML permitiendo tags seguros
     */
    public static function sanitizeHTML($html) {
        // Tags permitidos
        $allowed_tags = '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><a>';

        // Remover scripts y eventos
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi', '', $html);
        $html = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/gi', '', $html);
        $html = preg_replace('/on\w+\s*=\s*[^\s>]*/gi', '', $html);

        // Permitir solo tags permitidos
        $html = strip_tags($html, $allowed_tags);

        return $html;
    }

    /**
     * Escapa HTML para salida segura
     */
    public static function escapeHTML($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escapa para atributo HTML
     */
    public static function escapeAttribute($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escapa para JSON
     */
    public static function escapeJSON($data) {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APO | JSON_HEX_QUOT);
    }

    /**
     * Valida longitud de entrada
     */
    public static function validateLength($input, $max = 255, $min = 0) {
        $length = strlen($input);
        return $length >= $min && $length <= $max;
    }

    /**
     * Obtiene cabeceras de seguridad
     */
    public static function setSecurityHeaders() {
        // Prevenir clickjacking
        header('X-Frame-Options: SAMEORIGIN');

        // Prevenir MIME sniffing
        header('X-Content-Type-Options: nosniff');

        // XSS Protection (navegadores antiguos)
        header('X-XSS-Protection: 1; mode=block');

        // Content Security Policy (básica)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net;");

        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }

    /**
     * Valida JSON
     */
    public static function validateJSON($json) {
        if (is_string($json)) {
            json_decode($json);
            return json_last_error() === JSON_ERROR_NONE;
        }
        return false;
    }

    // ========== COOKIE MANAGEMENT ==========

    /**
     * Configura opciones seguras para las cookies de sesión
     */
    public static function configureSessionCookies() {
        // Configurar opciones de sesión antes de iniciar
        $options = [
            'lifetime' => 0,           // Muere cuando se cierra el navegador
            'path' => '/',             // Disponible en todo el sitio
            'domain' => '',            // Dominio actual
            'secure' => true,          // Solo HTTPS
            'httponly' => true,        // No accesible desde JavaScript
            'samesite' => 'Strict',    // Protección CSRF
        ];

        // Aplicar opciones si la sesión no ha iniciado
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params($options);
        }
    }

    /**
     * Crea una cookie segura
     * 
     * @param string $name Nombre de la cookie
     * @param string $value Valor de la cookie
     * @param int $expire Tiempo de expiración (segundos desde ahora, 0 para sesión)
     * @param bool $httponly Solo HTTP (protege contra XSS)
     * @param bool $secure Solo HTTPS
     */
    public static function setCookie($name, $value, $expire = 0, $httponly = true, $secure = true) {
        // Validar nombre de cookie
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            return false;
        }

        // Encriptar valor si es sensible
        $encrypted_value = self::encryptCookieValue($value);

        // Calcular tiempo de expiración
        $expiration = ($expire > 0) ? time() + $expire : 0;

        // Establecer cookie con opciones seguras
        return setcookie(
            $name,
            $encrypted_value,
            [
                'expires' => $expiration,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,           // Solo HTTPS
                'httponly' => $httponly,       // No accesible desde JS
                'samesite' => 'Strict',        // Protección CSRF
            ]
        );
    }

    /**
     * Obtiene una cookie desencriptada
     */
    public static function getCookie($name) {
        if (!isset($_COOKIE[$name])) {
            return null;
        }

        return self::decryptCookieValue($_COOKIE[$name]);
    }

    /**
     * Verifica si existe una cookie
     */
    public static function hasCookie($name) {
        return isset($_COOKIE[$name]);
    }

    /**
     * Elimina una cookie
     */
    public static function deleteCookie($name) {
        if (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
            return setcookie(
                $name,
                '',
                [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'httponly' => true,
                    'secure' => true,
                    'samesite' => 'Strict',
                ]
            );
        }
        return false;
    }

    /**
     * Encripta el valor de la cookie
     */
    private static function encryptCookieValue($value) {
        // Simple base64 encoding para protección básica
        // En producción, usa una librería de encriptación real
        return base64_encode($value);
    }

    /**
     * Desencripta el valor de la cookie
     */
    private static function decryptCookieValue($value) {
        try {
            return base64_decode($value, true);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Crea una cookie "Remember Me" para autenticación
     */
    public static function setRememberMeCookie($userId, $token, $days = 30) {
        $expire = $days * 24 * 60 * 60; // Convertir días a segundos
        
        $cookieData = json_encode([
            'user_id' => intval($userId),
            'token' => $token,
            'created' => time()
        ]);

        return self::setCookie('remember_me', $cookieData, $expire, true, true);
    }

    /**
     * Obtiene datos de cookie "Remember Me"
     */
    public static function getRememberMeCookie() {
        $cookie = self::getCookie('remember_me');
        
        if (!$cookie) {
            return null;
        }

        $data = json_decode($cookie, true);
        
        if (!$data || !isset($data['user_id']) || !isset($data['token'])) {
            return null;
        }

        return $data;
    }

    /**
     * Elimina cookie "Remember Me"
     */
    public static function deleteRememberMeCookie() {
        return self::deleteCookie('remember_me');
    }

    /**
     * Valida datos de cookie "Remember Me"
     */
    public static function validateRememberMeCookie($rememberData, $storedToken) {
        if (!$rememberData) {
            return false;
        }

        // Verificar que el token coincida
        if (!hash_equals($rememberData['token'], $storedToken)) {
            return false;
        }

        // Verificar que no sea demasiado antiguo (máx 90 días)
        if (time() - $rememberData['created'] > (90 * 24 * 60 * 60)) {
            return false;
        }

        return true;
    }

    /**
     * Limpia todas las cookies de la aplicación
     */
    public static function clearAllCookies() {
        if (empty($_COOKIE)) return;

        foreach ($_COOKIE as $name => $value) {
            self::deleteCookie($name);
        }
    }

    /**
     * Obtiene información de una cookie de forma segura
     */
    public static function getCookieInfo($name) {
        if (!isset($_COOKIE[$name])) {
            return null;
        }

        return [
            'name' => $name,
            'value' => self::getCookie($name),
            'exists' => true
        ];
    }
}
?>
