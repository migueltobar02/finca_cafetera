<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/SecurityManager.php';

class AuthController {
    private $usuarioModel;
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 900; // 15 minutos
    private const MAX_USERNAME_LENGTH = 50;
    private const MAX_PASSWORD_LENGTH = 255;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        SecurityManager::configureSessionCookies();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function sanitizeInput($input, $type = 'text') {
        return SecurityManager::sanitizeInput($input, $type);
    }

    private function validateUsername($username) {
        if (empty($username) || strlen($username) > self::MAX_USERNAME_LENGTH) {
            return false;
        }
        return preg_match('/^[a-zA-Z0-9_-]+$/', $username);
    }

    private function validatePassword($password) {
        return !empty($password) && strlen($password) <= self::MAX_PASSWORD_LENGTH;
    }

    private function isAccountLocked($username) {
        $key = 'login_attempts_' . md5($username);
        if (isset($_SESSION[$key])) {
            $attempts = $_SESSION[$key];
            if ($attempts['count'] >= self::MAX_LOGIN_ATTEMPTS) {
                if (time() - $attempts['time'] < self::LOCKOUT_TIME) {
                    return true;
                } else {
                    unset($_SESSION[$key]);
                }
            }
        }
        return false;
    }

    private function recordFailedAttempt($username) {
        $key = 'login_attempts_' . md5($username);
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
        }
        $_SESSION[$key]['count']++;
        $_SESSION[$key]['time'] = time();
    }

    private function clearFailedAttempts($username) {
        $key = 'login_attempts_' . md5($username);
        unset($_SESSION[$key]);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!SecurityManager::validateCSRFFromRequest()) {
                $_SESSION['error'] = 'Token de seguridad inválido.';
                header('Location: /login.php');
                exit;
            }

            $username = $this->sanitizeInput($_POST['username'] ?? '', 'text');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $_SESSION['error'] = 'Usuario y contraseña requeridos';
                header('Location: /login.php');
                exit;
            }

            if (!$this->validateUsername($username)) {
                $_SESSION['error'] = 'Usuario inválido';
                header('Location: /login.php');
                exit;
            }

            if (!$this->validatePassword($password)) {
                $_SESSION['error'] = 'Contraseña inválida';
                header('Location: /login.php');
                exit;
            }

            if ($this->isAccountLocked($username)) {
                $_SESSION['error'] = 'Cuenta bloqueada por intentos fallidos.';
                header('Location: /login.php');
                exit;
            }

            $usuario = $this->usuarioModel->findByUsername($username);

            if ($usuario && $this->usuarioModel->verifyPassword($password, $usuario['password_hash'])) {
                $this->clearFailedAttempts($username);

                $_SESSION['usuario'] = [
                    'id' => intval($usuario['id']),
                    'username' => $usuario['username'],
                    'nombre_completo' => $usuario['nombre_completo'],
                    'rol' => $usuario['rol']
                ];

                if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
                    $token = bin2hex(random_bytes(32));
                    SecurityManager::setRememberMeCookie($usuario['id'], $token, 30);
                }

                header('Location: /dashboard.php');
                exit;
            } else {
                $this->recordFailedAttempt($username);
                $_SESSION['error'] = 'Credenciales inválidas';
                header('Location: /login.php');
                exit;
            }
        }
    }

    public function logout() {
        SecurityManager::deleteRememberMeCookie();
        session_destroy();
        header('Location: /login.php');
        exit;
    }

    public function checkAuth() {
        SecurityManager::configureSessionCookies();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            $remember = SecurityManager::getRememberMeCookie();
            if ($remember) {
                $usuario = $this->usuarioModel->find($remember['user_id']);
                if ($usuario) {
                    $_SESSION['usuario'] = [
                        'id' => intval($usuario['id']),
                        'username' => $usuario['username'],
                        'nombre_completo' => $usuario['nombre_completo'],
                        'rol' => $usuario['rol']
                    ];
                    return $_SESSION['usuario'];
                } else {
                    SecurityManager::deleteRememberMeCookie();
                }
            }
            header('Location: /login.php');
            exit;
        }

        return $_SESSION['usuario'];
    }
}

if (isset($_GET['action'])) {
    $action = htmlspecialchars($_GET['action'], ENT_QUOTES, 'UTF-8');
    $auth = new AuthController();

    if ($action === 'login') {
        $auth->login();
    } elseif ($action === 'logout') {
        $auth->logout();
    }
}
?>
