<?php
/**
 * Página de inicio de sesión
 */

// Cargar autoloader primero
require_once '../app/autoload.php';

// Inicializar seguridad
SecurityManager::initSession();
SecurityManager::setSecurityHeaders();

$auth = new AuthController();

// Si ya está autenticado, redirigir al dashboard
if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php');
    exit;
}

// Mostrar mensajes de error si existen
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// Generar token CSRF
$csrf_token = SecurityManager::generateCSRFToken();

// El resto del código HTML permanece igual...
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Finca Cafetera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #8b4513 0%, #d2691e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .brand-logo {
            font-size: 2.5rem;
            color: #8b4513;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card p-4">
            <div class="card-body text-center">
                <div class="brand-logo">☕</div>
                <h3 class="card-title mb-4">Finca Cafetera</h3>
                <p class="text-muted mb-4">Sistema de Gestión Integral</p>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="../app/controllers/AuthController.php?action=login">
                    <!-- Token CSRF para prevenir CSRF attacks -->
                    <input type="hidden" name="_csrf_token" value="<?= SecurityManager::escapeAttribute($csrf_token) ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Ingrese su usuario" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Ingrese su contraseña" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                        <label class="form-check-label" for="remember_me">
                            Recuérdame por 30 días
                        </label>
                    </div>
                    
                    <button type="submit" class="btn w-100" 
                            style="background-color: #8b4513; color: white; padding: 12px;">
                        <strong>Iniciar Sesión</strong>
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <small class="text-muted">
                        ¿Problemas para acceder? Contacte al administrador del sistema.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Focus en el campo de usuario al cargar la página
        document.getElementById('username').focus();
        
        // Prevenir envío múltiple del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Iniciando sesión...';
        });
    </script>
</body>
</html>