<?php
/**
 * Header común para todas las páginas
 */
// `SecurityManager` y la sesión deben inicializarse por el entrypoint (p.ej. `app/autoload.php`)
// Aquí asumimos que la sesión ya está activa y `SecurityManager` disponible.
$usuario = $_SESSION['usuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Finca Cafetera' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .navbar {
            padding: 0.8rem 0;
        }
        
        .navbar-brand {
            font-size: 1.4rem;
            margin-right: 2rem;
        }
        
        .navbar-nav {
            align-items: center;
        }
        
        .nav-link {
            padding: 0.5rem 1rem !important;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: #ffd700 !important;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
        }
        
        .dropdown-item:hover {
            background-color: #f0f0f0;
            color: #8b4513;
        }
        
        .navbar-nav .dropdown-menu-end {
            right: 0 !important;
            left: auto !important;
        }
        
        @media (max-width: 991px) {
            .nav-link {
                padding: 0.5rem 0 !important;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #8b4513;">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <strong>☕ Finca Cafetera</strong>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><span>🏠</span> <span>Dashboard</span></a>
                    </li>
                    
                    <!-- Gestión de Personal -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="personalDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span>👥</span> <span>Personal</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="personalDropdown">
                            <li><a class="dropdown-item" href="empleados.php"><span>📋</span> <span>Empleados</span></a></li>
                            <li><a class="dropdown-item" href="jornales.php"><span>⏱️</span> <span>Jornales</span></a></li>
                        </ul>
                    </li>
                    
                    <!-- Gestión Financiera -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="financieraDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span>💰</span> <span>Financiera</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="financieraDropdown">
                            <li><a class="dropdown-item" href="ingresos.php"><span>💵</span> <span>Ingresos</span></a></li>
                            <li><a class="dropdown-item" href="egresos.php"><span>💸</span> <span>Egresos</span></a></li>
                        </ul>
                    </li>
                    
                    <!-- Producción y Ventas -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="produccionDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span>🌱</span> <span>Producción</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="produccionDropdown">
                            <li><a class="dropdown-item" href="cosechas.php"><span>🌾</span> <span>Cosechas</span></a></li>
                            <li><a class="dropdown-item" href="ventas.php"><span>🛒</span> <span>Ventas</span></a></li>
                        </ul>
                    </li>
                    
                    <!-- Catálogos -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="catalogosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span>📚</span> <span>Catálogos</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="catalogosDropdown">
                            <li><a class="dropdown-item" href="clientes.php"><span>👥</span> <span>Clientes</span></a></li>
                        </ul>
                    </li>
                    
                    <!-- Reportes -->
                    <li class="nav-item">
                        <a class="nav-link" href="reportes.php"><span>📊</span> <span>Reportes</span></a>
                    </li>
                </ul>
                
                <?php if ($usuario): ?>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usuarioDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span>👤</span> <span><?= htmlspecialchars($usuario['nombre_completo']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="usuarioDropdown">
                            <li><span class="dropdown-item-text">
                                <small>Rol: <span class="badge bg-primary"><?= ucfirst($usuario['rol']) ?></span></small>
                            </span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../app/controllers/AuthController.php?action=logout">
                                <span>🚪</span> <span>Cerrar sesión</span>
                            </a></li>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Helper function para CSRF token en formularios -->
    <?php
    if (!function_exists('csrfToken')) {
        function csrfToken() {
            $token = SecurityManager::getCSRFToken();
            return '<input type="hidden" name="_csrf_token" value="' . SecurityManager::escapeAttribute($token) . '">';
        }
    }
    ?>
            </div>
        </div>
    </nav>