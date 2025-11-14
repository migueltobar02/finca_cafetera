<?php
/**
 * Dashboard principal del sistema
 */

// Cargar autoloader primero
require_once '../app/autoload.php';

$auth = new AuthController();
$usuario = $auth->checkAuth();

$dashboardController = new DashboardController();
$estadisticas = $dashboardController->getEstadisticas();
$actividadReciente = $dashboardController->getActividadReciente();
$topClientes = $dashboardController->getTopClientes();
$proximasCosechas = $dashboardController->getProximasCosechas();
$jornalesPendientes = $dashboardController->getJornalesPendientes();

// Establecer título para el header
$titulo = 'Dashboard - Finca Cafetera';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: none;
            border-radius: 12px;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .mini-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        .activity-item {
            border-left: 4px solid #007bff;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        .activity-item.egreso {
            border-left-color: #dc3545;
        }
        .section-title {
            color: #8b4513;
            border-bottom: 2px solid #8b4513;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include '../app/views/components/header.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>

        <!-- Tarjetas de Estadísticas Principales -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">INGRESOS DEL MES</h6>
                                <h2 class="card-text">$<?= number_format($estadisticas['ingresos_mes'], 0, ',', '.') ?></h2>
                            </div>
                            <div class="align-self-center">
                                <span style="font-size: 2rem;">💰</span>
                            </div>
                        </div>
                        <p class="card-text mb-0">Total de ingresos registrados</p>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">EGRESOS DEL MES</h6>
                                <h2 class="card-text">$<?= number_format($estadisticas['egresos_mes'], 0, ',', '.') ?></h2>
                            </div>
                            <div class="align-self-center">
                                <span style="font-size: 2rem;">💸</span>
                            </div>
                        </div>
                        <p class="card-text mb-0">Total de gastos registrados</p>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">UTILIDAD NETA</h6>
                                <h2 class="card-text">$<?= number_format($estadisticas['utilidad_mes'], 0, ',', '.') ?></h2>
                            </div>
                            <div class="align-self-center">
                                <span style="font-size: 2rem;">📈</span>
                            </div>
                        </div>
                        <p class="card-text mb-0">
                            Margen: <?= number_format($estadisticas['margen_mes'], 1) ?>%
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">VENTAS DEL MES</h6>
                                <h2 class="card-text">$<?= number_format($estadisticas['ventas_mes'], 0, ',', '.') ?></h2>
                            </div>
                            <div class="align-self-center">
                                <span style="font-size: 2rem;">🛒</span>
                            </div>
                        </div>
                        <p class="card-text mb-0">Total en ventas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Segunda fila de estadísticas -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card text-white bg-info">
                    <div class="card-body text-center">
                        <h6>CAFÉ COSECHADO</h6>
                        <h3><?= number_format($estadisticas['cafe_cosechado'], 0, ',', '.') ?> kg</h3>
                        <p class="mb-0">Este mes</p>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card text-white bg-secondary">
                    <div class="card-body text-center">
                        <h6>CAFÉ VENDIDO</h6>
                        <h3><?= number_format($estadisticas['cafe_vendido'], 0, ',', '.') ?> kg</h3>
                        <p class="mb-0">Este mes</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card text-dark bg-light">
                    <div class="card-body text-center">
                        <h6>EMPLEADOS</h6>
                        <h3><?= number_format($estadisticas['total_empleados'], 0, ',', '.') ?></h3>
                        <p class="mb-0">Activos</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card text-white bg-dark">
                    <div class="card-body text-center">
                        <h6>CLIENTES</h6>
                        <h3><?= number_format($estadisticas['total_clientes'], 0, ',', '.') ?></h3>
                        <p class="mb-0">Registrados</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card text-white" style="background-color: #8b4513;">
                    <div class="card-body text-center">
                        <h6>PAGOS JORNALES</h6>
                        <h3>$<?= number_format($estadisticas['total_pagos_jornales'], 0, ',', '.') ?></h3>
                        <p class="mb-0">Este mes</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card text-white bg-success">
                    <div class="card-body text-center">
                        <h6>JORNALES PEND.</h6>
                        <h3><?= number_format($jornalesPendientes, 0, ',', '.') ?></h3>
                        <p class="mb-0">Por pagar</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actividad Reciente - Full Width -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="section-title mb-0">📋 Actividad Reciente</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($actividadReciente)): ?>
                            <?php foreach ($actividadReciente as $actividad): ?>
                            <div class="activity-item <?= $actividad['tipo'] == 'egreso' ? 'egreso' : '' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($actividad['descripcion']) ?></h6>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($actividad['fecha'])) ?>
                                            <?php if ($actividad['proveedor']): ?>
                                                • <?= htmlspecialchars($actividad['proveedor']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $actividad['tipo'] == 'ingreso' ? 'success' : 'danger' ?>">
                                            $<?= number_format($actividad['monto'], 0, ',', '.') ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?= $actividad['tipo'] == 'ingreso' ? 'Ingreso' : 'Egreso' ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <p>No hay actividad reciente para mostrar</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recordatorios - Full Width -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="section-title mb-0">🔔 Recordatorios</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($jornalesPendientes > 0): ?>
                        <div class="alert alert-warning">
                            <strong>📋 Pendiente:</strong> Tienes <?= $jornalesPendientes ?> pagos de jornales pendientes.
                        </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <strong>📊 Reporte:</strong> Genera el reporte mensual para análisis.
                        </div>
                        
                        <div class="alert alert-success">
                            <strong>✅ Sistema:</strong> Todas las funcionalidades activas.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lotes Activos - Full Width -->
        <div class="row mb-4">
            <div class="col-12">
                <?php if (!empty($proximasCosechas)): ?>
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="section-title mb-0">🌱 Lotes Activos</h4>
                    </div>
                    <div class="card-body">
                        <?php foreach ($proximasCosechas as $lote): ?>
                        <div class="mini-card">
                            <h6 class="mb-1"><?= htmlspecialchars($lote['nombre']) ?></h6>
                            <small class="text-muted d-block"><?= $lote['codigo_lote'] ?></small>
                            <small class="text-muted"><?= $lote['variedad_cafe'] ?></small>
                            <?php if ($lote['ultima_cosecha']): ?>
                            <div class="mt-1">
                                <small class="text-success">
                                    Última cosecha: <?= date('d/m/Y', strtotime($lote['ultima_cosecha'])) ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>