<?php
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/ReportesController.php';

$auth = new AuthController();
$usuario = $auth->checkAuth();

$reportesController = new ReportesController();

// Parámetros por defecto
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
$tipoReporte = $_GET['tipo_reporte'] ?? 'financiero';

// Generar reporte
$reporteData = [];
if (isset($_GET['generar'])) {
    switch ($tipoReporte) {
        case 'financiero':
            $reporteData = $reportesController->generarReporteFinanciero($fechaInicio, $fechaFin);
            break;
        case 'produccion':
            $reporteData = $reportesController->generarReporteProduccion($fechaInicio, $fechaFin);
            break;
    }
}

// Obtener resumen mensual
$resumenMensual = $reportesController->getResumenMensual(date('m'), date('Y'));

// Obtener comparativo CORREGIDO: usar método simple
try {
    $comparativo = $reportesController->getComparativoSimple(6);
} catch (Exception $e) {
    $comparativo = [];
    error_log("Error obteniendo comparativo: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Análisis - Finca Cafetera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php 
    $titulo = 'Reportes y Análisis';
    include __DIR__ . '/app/views/components/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">📊 Reportes y Análisis</h2>
                    </div>
                    <div class="card-body">
                        <!-- Filtros de reportes -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Generar Reporte</h5>
                                        <form method="GET" action="reportes.php">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tipo de Reporte</label>
                                                        <select class="form-select" name="tipo_reporte">
                                                            <option value="financiero" <?= $tipoReporte == 'financiero' ? 'selected' : '' ?>>Reporte Financiero</option>
                                                            <option value="produccion" <?= $tipoReporte == 'produccion' ? 'selected' : '' ?>>Reporte de Producción</option>
                                                            <option value="ventas" <?= $tipoReporte == 'ventas' ? 'selected' : '' ?>>Reporte de Ventas</option>
                                                            <option value="jornales" <?= $tipoReporte == 'jornales' ? 'selected' : '' ?>>Reporte de Jornales</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Fecha Inicio</label>
                                                        <input type="date" class="form-control" name="fecha_inicio" value="<?= $fechaInicio ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Fecha Fin</label>
                                                        <input type="date" class="form-control" name="fecha_fin" value="<?= $fechaFin ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">&nbsp;</label>
                                                        <div>
                                                            <button type="submit" name="generar" value="1" class="btn btn-primary w-100">
                                                                Generar Reporte
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen del Mes Actual -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">📈 Resumen del Mes Actual</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="card text-white bg-primary">
                                                    <div class="card-body text-center">
                                                        <h6>Ingresos</h6>
                                                        <h4>$<?= number_format($resumenMensual['ingresos'], 0, ',', '.') ?></h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="card text-white bg-danger">
                                                    <div class="card-body text-center">
                                                        <h6>Egresos</h6>
                                                        <h4>$<?= number_format($resumenMensual['egresos'], 0, ',', '.') ?></h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="card text-white bg-success">
                                                    <div class="card-body text-center">
                                                        <h6>Utilidad</h6>
                                                        <h4>$<?= number_format($resumenMensual['utilidad'], 0, ',', '.') ?></h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="card text-white bg-warning">
                                                    <div class="card-body text-center">
                                                        <h6>Cosechas</h6>
                                                        <h4><?= number_format($resumenMensual['cosechas'], 0, ',', '.') ?> kg</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="card text-white bg-info">
                                                    <div class="card-body text-center">
                                                        <h6>Ventas</h6>
                                                        <h4><?= number_format($resumenMensual['ventas']['total_kilos'] ?? 0, 0, ',', '.') ?> kg</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="card text-white bg-secondary">
                                                    <div class="card-body text-center">
                                                        <h6>Margen</h6>
                                                        <h4>
                                                            <?= $resumenMensual['ingresos'] > 0 ? 
                                                                number_format(($resumenMensual['utilidad'] / $resumenMensual['ingresos']) * 100, 1) : 0 ?>%
                                                        </h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reporte Generado -->
                        <?php if (!empty($reporteData)): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            Reporte <?= match($tipoReporte) {
                                                'financiero' => 'Financiero',
                                                'produccion' => 'de Producción',
                                                'ventas' => 'de Ventas',
                                                'jornales' => 'de Jornales'
                                            } ?>
                                        </h5>
                                        <div>
                                            <button class="btn btn-sm btn-success" onclick="exportarPDF()">
                                                📄 Exportar PDF
                                            </button>
                                            <button class="btn btn-sm btn-primary" onclick="exportarExcel()">
                                                📊 Exportar Excel
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <?php if ($tipoReporte == 'financiero'): ?>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Tipo</th>
                                                        <th>Descripción</th>
                                                        <th>Proveedor/Cliente</th>
                                                        <th>Monto</th>
                                                    </tr>
                                                    <?php elseif ($tipoReporte == 'produccion'): ?>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Lote</th>
                                                        <th>Variedad</th>
                                                        <th>Kilos</th>
                                                        <th>Calidad</th>
                                                        <th>Rendimiento</th>
                                                    </tr>
                                                    <?php endif; ?>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($reporteData as $fila): ?>
                                                    <tr>
                                                        <?php if ($tipoReporte == 'financiero'): ?>
                                                        <td><?= date('d/m/Y', strtotime($fila['fecha'])) ?></td>
                                                        <td>
                                                            <span class="badge bg-<?= $fila['tipo'] == 'ingresos' ? 'success' : 'danger' ?>">
                                                                <?= ucfirst($fila['tipo']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars($fila['descripcion']) ?></td>
                                                        <td><?= $fila['proveedor'] ?: 'N/A' ?></td>
                                                        <td class="<?= $fila['tipo'] == 'ingresos' ? 'text-success' : 'text-danger' ?> fw-bold">
                                                            $<?= number_format($fila['monto'], 0, ',', '.') ?>
                                                        </td>
                                                        <?php elseif ($tipoReporte == 'produccion'): ?>
                                                        <td><?= date('d/m/Y', strtotime($fila['fecha_cosecha'])) ?></td>
                                                        <td><?= $fila['lote'] ?></td>
                                                        <td><?= $fila['variedad_cafe'] ?></td>
                                                        <td class="fw-bold"><?= number_format($fila['kilos_cosechados'], 0, ',', '.') ?> kg</td>
                                                        <td>
                                                            <span class="badge bg-<?= match($fila['calidad']) {
                                                                'premium' => 'success',
                                                                'especial' => 'info',
                                                                'estandar' => 'primary',
                                                                'comercial' => 'secondary'
                                                            } ?>">
                                                                <?= ucfirst($fila['calidad']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= number_format($fila['rendimiento'] ?? 0, 1, ',', '.') ?> kg/ha</td>
                                                        <?php endif; ?>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Comparativo Mensual -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">📅 Comparativo Mensual (Últimos 6 meses)</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Mes/Año</th>
                                                        <th>Ingresos</th>
                                                        <th>Egresos</th>
                                                        <th>Utilidad</th>
                                                        <th>Margen</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($comparativo)): ?>
                                                        <?php foreach ($comparativo as $mes): ?>
                                                        <tr>
                                                            <td><?= $mes['mes_nombre'] ?>/<?= $mes['ano'] ?></td>
                                                            <td class="text-success fw-bold">$<?= number_format($mes['ingresos'], 0, ',', '.') ?></td>
                                                            <td class="text-danger">$<?= number_format($mes['egresos'], 0, ',', '.') ?></td>
                                                            <td class="fw-bold <?= $mes['utilidad'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                                $<?= number_format($mes['utilidad'], 0, ',', '.') ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-<?= $mes['margen'] >= 20 ? 'success' : ($mes['margen'] >= 0 ? 'warning' : 'danger') ?>">
                                                                    <?= number_format($mes['margen'], 1) ?>%
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-4">
                                                                No hay datos de comparativo disponibles
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script>
    function exportarPDF() {
        alert('Funcionalidad de exportación PDF en desarrollo');
        // En una implementación real, aquí se generaría el PDF
    }

    function exportarExcel() {
        alert('Funcionalidad de exportación Excel en desarrollo');
        // En una implementación real, aquí se generaría el Excel
    }
    </script>
</body>
</html>