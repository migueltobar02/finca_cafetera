<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/SecurityManager.php';

class AuthController {
    private $usuarioModel;
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 900; // 15 minutos en segundos
    private const MAX_USERNAME_LENGTH = 50;
    private const MAX_PASSWORD_LENGTH = 255;

    public function __construct() {
        // Cargar el modelo de usuario
        $this->usuarioModel = new Usuario();
        
        // Configurar cookies de sesión seguras
        SecurityManager::configureSessionCookies();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Sanitiza y valida entrada del usuario usando SecurityManager
     */
    private function sanitizeInput($input, $type = 'text') {
        return SecurityManager::sanitizeInput($input, $type);
    }

    /**
     * Valida la longitud y formato del username
     */
    private function validateUsername($username) {
        if (empty($username) || strlen($username) > self::MAX_USERNAME_LENGTH) {
            return false;
        }
        // Solo alfanuméricos, guiones y guiones bajos
        return preg_match('/^[a-zA-Z0-9_-]+$/', $username);
    }

    /**
     * Valida la longitud de la contraseña
     */
    private function validatePassword($password) {
        return !empty($password) && strlen($password) <= self::MAX_PASSWORD_LENGTH;
    }

    /**
     * Verifica si la cuenta está bloqueada por intentos fallidos
     */
    private function isAccountLocked($username) {
        $lockoutKey = 'login_attempts_' . md5($username);
        
        if (isset($_SESSION[$lockoutKey])) {
            $attempts = $_SESSION[$lockoutKey];
            
            if ($attempts['count'] >= self::MAX_LOGIN_ATTEMPTS) {
                if (time() - $attempts['time'] < self::LOCKOUT_TIME) {
                    return true;
                } else {
                    // El tiempo de bloqueo ha pasado, resetear intentos
                    unset($_SESSION[$lockoutKey]);
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Registra un intento de login fallido
     */
    private function recordFailedAttempt($username) {
        $lockoutKey = 'login_attempts_' . md5($username);
        
        if (!isset($_SESSION[$lockoutKey])) {
            $_SESSION[$lockoutKey] = ['count' => 0, 'time' => time()];
        }
        
        $_SESSION[$lockoutKey]['count']++;
        $_SESSION[$lockoutKey]['time'] = time();
    }

    /**
     * Limpia los intentos fallidos después de login exitoso
     */
    private function clearFailedAttempts($username) {
        $lockoutKey = 'login_attempts_' . md5($username);
        unset($_SESSION[$lockoutKey]);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar CSRF token
            if (!SecurityManager::validateCSRFFromRequest()) {
                $_SESSION['error'] = 'Token de seguridad inválido. Por favor, intenta de nuevo.';
                header('Location: /finca_cafetera/public/login.php');
                exit;
            }

            // Sanitizar entrada
            $username = $this->sanitizeInput($_POST['username'] ?? '', 'text');
            $password = trim($_POST['password'] ?? ''); // Trim pero no sanitizar contraseña

            // Validar que no esté vacío
            if (empty($username) || empty($password)) {
                $_SESSION['error'] = 'Usuario y contraseña son requeridos';
                header('Location: /finca_cafetera/public/login.php');
                exit;
            }

            // Validar formato del username
            if (!$this->validateUsername($username)) {
                $_SESSION['error'] = 'Usuario inválido';
                header('Location: /finca_cafetera/public/login.php');
                exit;
            }

            // Validar longitud de contraseña
            if (!$this->validatePassword($password)) {
                $_SESSION['error'] = 'Contraseña inválida';
                header('Location: /finca_cafetera/public/login.php');
                exit;
            }

            // Verificar si la cuenta está bloqueada
            if ($this->isAccountLocked($username)) {
                $_SESSION['error'] = 'Cuenta bloqueada. Intenta de nuevo en 15 minutos.';
                header('Location: /finca_cafetera/public/login.php');
                exit;
            }

            // Usar prepared statement para evitar SQL injection
            $usuario = $this->usuarioModel->findByUsername($username);
            
            if ($usuario && $this->usuarioModel->verifyPassword($password, $usuario['password_hash'])) {
                // Login exitoso
                $this->clearFailedAttempts($username);
                
                $_SESSION['usuario'] = [
                    'id' => intval($usuario['id']),
                    'username' => $usuario['username'],
                    'nombre_completo' => $usuario['nombre_completo'],
                    'rol' => $usuario['rol']
                ];
                
                // Manejo de "Recuérdame"
                if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
                    // Generar token único para remember me
                    $rememberToken = bin2hex(random_bytes(32));
                    
                    // Guardar token en base de datos (si existe tabla de tokens)
                    // $this->usuarioModel->saveRememberToken($usuario['id'], $rememberToken);
                    
                    // Crear cookie de remember me
                    SecurityManager::setRememberMeCookie($usuario['id'], $rememberToken, 30);
                }
                
                // Registrar login en log (opcional)
                error_log("[LOGIN] Usuario {$username} inició sesión correctamente - " . date('Y-m-d H:i:s'));
                
                header('Location: /finca_cafetera/public/dashboard.php');
                exit;
            } else {
                // Login fallido
                $this->recordFailedAttempt($username);
                
                // Log de intento fallido (sin exponer información sensible)
                error_log("[LOGIN_FAILED] Intento fallido para usuario {$username} - " . date('Y-m-d H:i:s'));
                
                $_SESSION['error'] = 'Credenciales inválidas';
                header('Location: /finca_cafetera/public/login.php');
                exit;
            }
        }
    }

    public function logout() {
        // Limpiar cookies de "Remember Me"
        SecurityManager::deleteRememberMeCookie();
        
        session_destroy();
        header('Location: /finca_cafetera/public/login.php');
        exit;
    }

    public function checkAuth() {
        // Configurar cookies de sesión
        SecurityManager::configureSessionCookies();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario'])) {
            // Intentar usar "Remember Me" cookie
            $rememberData = SecurityManager::getRememberMeCookie();
            
            if ($rememberData) {
                // Cargar usuario desde la cookie
                $usuario = $this->usuarioModel->find($rememberData['user_id']);
                
                if ($usuario) {
                    // Restaurar sesión del usuario
                    $_SESSION['usuario'] = [
                        'id' => intval($usuario['id']),
                        'username' => $usuario['username'],
                        'nombre_completo' => $usuario['nombre_completo'],
                        'rol' => $usuario['rol']
                    ];
                    
                    error_log("[AUTO_LOGIN] Usuario {$usuario['username']} restaurado desde Remember Me - " . date('Y-m-d H:i:s'));
                    
                    return $_SESSION['usuario'];
                } else {
                    // Cookie inválida, eliminarla
                    SecurityManager::deleteRememberMeCookie();
                }
            }
            
            header('Location: /finca_cafetera/public/login.php');
            exit;
        }
        
        return $_SESSION['usuario'];
    }
}

// Manejar acciones del controlador
if (isset($_GET['action'])) {
    $action = htmlspecialchars($_GET['action'], ENT_QUOTES, 'UTF-8');
    $authController = new AuthController();
    
    if ($action === 'login') {
        $authController->login();
    } elseif ($action === 'logout') {
        $authController->logout();
    }
}
?>