<?php
// dashboard.php

require_once __DIR__ . '/app/autoload.php';

// AUTENTICACIÓN
$auth = new AuthController();
$usuario = $auth->checkAuth(); // Redirige al login si no hay sesión

// CONTROLADOR DASHBOARD
$dashboardController = new DashboardController();
$estadisticas       = $dashboardController->getEstadisticas();
$actividadReciente  = $dashboardController->getActividadReciente();
$topClientes        = $dashboardController->getTopClientes();
$proximasCosechas   = $dashboardController->getProximasCosechas();
$jornalesPendientes = $dashboardController->getJornalesPendientes();

// TÍTULO
$titulo = 'Dashboard - Finca Cafetera';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; border: none; border-radius: 12px; height: 100%; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .mini-card { background: white; border-radius: 8px; padding: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 15px; }
        .activity-item { border-left: 4px solid #007bff; padding-left: 15px; margin-bottom: 15px; }
        .activity-item.egreso { border-left-color: #dc3545; }
        .section-title { color: #8b4513; border-bottom: 2px solid #8b4513; padding-bottom: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/app/views/components/header.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>

        <!-- TARJETAS ESTADÍSTICAS -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">INGRESOS DEL MES</h6>
                                <h2 class="card-text">$<?= number_format($estadisticas['ingresos_mes'] ?? 0, 0, ',', '.') ?></h2>
                            </div>
                            <div class="align-self-center"><span style="font-size: 2rem;">💰</span></div>
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
                                <h2 class="card-text">$<?= number_format($estadisticas['egresos_mes'] ?? 0, 0, ',', '.') ?></h2>
                            </div>
                            <div class="align-self-center"><span style="font-size: 2rem;">💸</span></div>
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
                                <h2 class="card-text">$<?= number_format($estadisticas['utilidad_mes'] ?? 0, 0, ',', '.') ?></h2>
                            </div>
                            <div class="align-self-center"><span style="font-size: 2rem;">📈</span></div>
                        </div>
                        <p class="card-text mb-0">
                            Margen: <?= number_format($estadisticas['margen_mes'] ?? 0, 1) ?>%
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
                                <h2 class="card-text">$<?= number_format($estadisticas['ventas_mes'] ?? 0, 0, ',', '.') ?></h2>
                            </div>
                            <div class="align-self-center"><span style="font-size: 2rem;">🛒</span></div>
                        </div>
                        <p class="card-text mb-0">Total en ventas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- OTRAS ESTADÍSTICAS -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card text-white bg-info text-center">
                    <h6>CAFÉ COSECHADO</h6>
                    <h3><?= number_format($estadisticas['cafe_cosechado'] ?? 0, 0, ',', '.') ?> kg</h3>
                    <p class="mb-0">Este mes</p>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card text-white bg-secondary text-center">
                    <h6>CAFÉ VENDIDO</h6>
                    <h3><?= number_format($estadisticas['cafe_vendido'] ?? 0, 0, ',', '.') ?> kg</h3>
                    <p class="mb-0">Este mes</p>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card text-dark bg-light text-center">
                    <h6>EMPLEADOS</h6>
                    <h3><?= number_format($estadisticas['total_empleados'] ?? 0, 0, ',', '.') ?></h3>
                    <p class="mb-0">Activos</p>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card text-white bg-dark text-center">
                    <h6>CLIENTES</h6>
                    <h3><?= number_format($estadisticas['total_clientes'] ?? 0, 0, ',', '.') ?></h3>
                    <p class="mb-0">Registrados</p>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card text-white" style="background-color: #8b4513; text-align:center;">
                    <h6>PAGOS JORNALES</h6>
                    <h3>$<?= number_format($estadisticas['total_pagos_jornales'] ?? 0, 0, ',', '.') ?></h3>
                    <p class="mb-0">Este mes</p>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card text-white bg-success text-center">
                    <h6>JORNALES PEND.</h6>
                    <h3><?= number_format(count($jornalesPendientes) ?? 0, 0, ',', '.') ?></h3>
                    <p class="mb-0">Por pagar</p>
                </div>
            </div>
        </div>

        <!-- ACTIVIDAD RECIENTE -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="section-title mb-0">📋 Actividad Reciente</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($actividadReciente)): ?>
                            <?php foreach($actividadReciente as $act): ?>
                                <div class="activity-item <?= ($act['tipo'] ?? '') === 'egreso' ? 'egreso' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($act['descripcion'] ?? '') ?></h6>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($act['fecha_creacion'] ?? '')) ?></small>
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

        <!-- TOP CLIENTES -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="section-title mb-0">🏆 Top Clientes</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($topClientes)): ?>
                            <?php foreach($topClientes as $cliente): ?>
                                <div class="mini-card">
                                    <?= htmlspecialchars(($cliente['nombres'] ?? '') . ' ' . ($cliente['apellidos'] ?? '')) ?> -
                                    Total Ventas: <?= number_format($cliente['total_ventas'] ?? 0, 0, ',', '.') ?> •
                                    Monto: $<?= number_format($cliente['total_monto'] ?? 0, 0, ',', '.') ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No hay clientes para mostrar</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
