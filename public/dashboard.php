<?php
// -------------------------------------------------------------
// dashboard.php
// -------------------------------------------------------------

// Autoload y control de autenticación
require_once __DIR__ . '/app/autoload.php';

$auth = new AuthController();
$usuario = $auth->checkAuth(); // Redirige al login si no hay sesión

// Controlador de dashboard
$dashboardController = new DashboardController();
$estadisticas        = $dashboardController->getEstadisticas();
$actividadReciente   = $dashboardController->getActividadReciente();
$topClientes         = $dashboardController->getTopClientes();
$proximasCosechas    = $dashboardController->getProximasCosechas();
$jornalesPendientes  = $dashboardController->getJornalesPendientes();

// Establecer título
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
    <?php
    // Header: usa require_once para no generar salida innecesaria
    $headerFile = __DIR__ . '/app/views/components/header.php';
    if (file_exists($headerFile)) {
        require_once $headerFile;
    }
    ?>

    <div class="container-fluid mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>

        <!-- Tarjetas de Estadísticas Principales -->
        <div class="row mb-4">
            <?php
            $estadisticasDefaults = [
                'ingresos_mes' => 0,
                'egresos_mes' => 0,
                'utilidad_mes' => 0,
                'margen_mes' => 0,
                'ventas_mes' => 0,
                'cafe_cosechado' => 0,
                'cafe_vendido' => 0,
                'total_empleados' => 0,
                'total_clientes' => 0,
                'total_pagos_jornales' => 0
            ];
            $estadisticas = array_merge($estadisticasDefaults, $estadisticas);
            ?>
            <!-- INGRESOS -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-primary">
                    <div class="card-body d-flex justify-content-between">
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
            <!-- EGRESOS -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-danger">
                    <div class="card-body d-flex justify-content-between">
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
            <!-- UTILIDAD -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-success">
                    <div class="card-body d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">UTILIDAD NETA</h6>
                            <h2 class="card-text">$<?= number_format($estadisticas['utilidad_mes'], 0, ',', '.') ?></h2>
                        </div>
                        <div class="align-self-center">
                            <span style="font-size: 2rem;">📈</span>
                        </div>
                    </div>
                    <p class="card-text mb-0">Margen: <?= number_format($estadisticas['margen_mes'], 1) ?>%</p>
                </div>
            </div>
            <!-- VENTAS -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card text-white bg-warning">
                    <div class="card-body d-flex justify-content-between">
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

        <!-- Segunda fila de estadísticas -->
        <div class="row mb-4">
            <?php
            $segundasStats = [
                ['label'=>'CAFÉ COSECHADO','value'=>$estadisticas['cafe_cosechado'],'unit'=>'kg','bg'=>'info','text'=>'Este mes'],
                ['label'=>'CAFÉ VENDIDO','value'=>$estadisticas['cafe_vendido'],'unit'=>'kg','bg'=>'secondary','text'=>'Este mes'],
                ['label'=>'EMPLEADOS','value'=>$estadisticas['total_empleados'],'unit'=>'','bg'=>'light','text'=>'Activos','text_color'=>'text-dark'],
                ['label'=>'CLIENTES','value'=>$estadisticas['total_clientes'],'unit'=>'','bg'=>'dark','text'=>'Registrados'],
                ['label'=>'PAGOS JORNALES','value'=>$estadisticas['total_pagos_jornales'],'unit'=>'','bg'=>'#8b4513','text'=>'Este mes'],
                ['label'=>'JORNALES PEND.','value'=>count($jornalesPendientes),'unit'=>'','bg'=>'success','text'=>'Por pagar']
            ];
            foreach($segundasStats as $stat):
                $bgClass = isset($stat['bg']) ? $stat['bg'] : 'bg-light';
                $textColor = isset($stat['text_color']) ? $stat['text_color'] : 'text-white';
            ?>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card stat-card <?= $textColor ?>" style="background-color: <?= ($bgClass[0]==='#')?$bgClass:'' ?>;<?= ($bgClass[0]!=='#')?'':'color:white;' ?>">
                    <div class="card-body text-center">
                        <h6><?= $stat['label'] ?></h6>
                        <h3><?= number_format($stat['value'],0,',','.') ?> <?= $stat['unit'] ?></h3>
                        <p class="mb-0"><?= $stat['text'] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Actividad Reciente -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="section-title mb-0">📋 Actividad Reciente</h4>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($actividadReciente)): ?>
                            <?php foreach($actividadReciente as $act): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6><?= htmlspecialchars($act['descripcion'] ?? $act['nombre']) ?></h6>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($act['fecha_creacion'] ?? '')) ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">No hay actividad reciente</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Clientes -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="section-title mb-0">🏆 Top Clientes</h4>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($topClientes)): ?>
                            <?php foreach($topClientes as $cliente): ?>
                                <div class="mini-card">
                                    <?= htmlspecialchars($cliente['nombres'] . ' ' . $cliente['apellidos']) ?> -
                                    Total Ventas: <?= number_format($cliente['total_ventas'],0,',','.') ?> •
                                    Monto: $<?= number_format($cliente['total_monto'],0,',','.') ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-2">No hay clientes destacados</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Próximas Cosechas -->
        <div class="row mb-4">
            <div class="col-12">
                <?php if(!empty($proximasCosechas)): ?>
                    <div class="card">
                        <div class="card-header bg-white">
                            <h4 class="section-title mb-0">🌱 Próximas Cosechas</h4>
                        </div>
                        <div class="card-body">
                            <?php foreach($proximasCosechas as $cosecha): ?>
                                <div class="mini-card">
                                    <h6><?= htmlspecialchars($cosecha['lote_id'] ?? 'Lote') ?></h6>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($cosecha['fecha_cosecha'] ?? '')) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
