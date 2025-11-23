<?php
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/CosechasController.php';

$auth = new AuthController();
$usuario = $auth->checkAuth();

$cosechasController = new CosechasController();
$cosechas = $cosechasController->index();
$lotes = $cosechasController->obtenerLotes();
$estadisticas = $cosechasController->getEstadisticas();

// Manejar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'crear') {
        $data = [
            'lote_id' => $_POST['lote_id'],
            'fecha_cosecha' => $_POST['fecha_cosecha'],
            'kilos_cosechados' => $_POST['kilos_cosechados'],
            'calidad' => $_POST['calidad'],
            'observaciones' => $_POST['observaciones']
        ];
        
        if ($cosechasController->crear($data)) {
            $_SESSION['success'] = 'Cosecha registrada exitosamente';
            header('Location: cosechas.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error al registrar cosecha';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cosechas - Finca Cafetera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include __DIR__ . '/app/views/components/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">🌱 Registro de Cosechas</h2>
                    </div>
                    <div class="card-body">
                        <!-- Formulario de cosecha -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Registrar Nueva Cosecha</h5>
                                        <form method="POST" action="cosechas.php">
                                            <input type="hidden" name="action" value="crear">
                                            
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Lote *</label>
                                                        <select class="form-select" name="lote_id" required>
                                                            <option value="">Seleccionar lote...</option>
                                                            <?php foreach ($lotes as $lote): ?>
                                                            <option value="<?= $lote['id'] ?>">
                                                                <?= $lote['nombre'] ?> (<?= $lote['codigo_lote'] ?>)
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Fecha de Cosecha *</label>
                                                        <input type="date" class="form-control" name="fecha_cosecha" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Kilos Cosechados *</label>
                                                        <input type="number" class="form-control" name="kilos_cosechados" step="0.01" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Calidad *</label>
                                                        <select class="form-select" name="calidad" required>
                                                            <option value="premium">Premium</option>
                                                            <option value="especial">Especial</option>
                                                            <option value="estandar" selected>Estándar</option>
                                                            <option value="comercial">Comercial</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Rendimiento Estimado</label>
                                                        <input type="text" class="form-control" readonly id="rendimientoEstimado">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Observaciones</label>
                                                <textarea class="form-control" name="observaciones" rows="3"></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-success">Registrar Cosecha</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mensajes de alerta -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>

                        <!-- Estadísticas -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Mes</h5>
                                        <h2 class="card-text">
                                            <?php 
                                                $total = is_array($estadisticas['total_cosechado']) 
                                                    ? ($estadisticas['total_cosechado']['total'] ?? 0) 
                                                    : $estadisticas['total_cosechado'];
                                            ?>
                                            <?= number_format($total, 0, ',', '.') ?> kg
                                        </h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-info">
                                    <div class="card-body">
                                        <h5 class="card-title">Rendimiento Promedio</h5>
                                        <h2 class="card-text">
                                            <?php 
                                                $prom = is_array($estadisticas['rendimiento_promedio']) 
                                                    ? ($estadisticas['rendimiento_promedio']['promedio'] ?? 0)
                                                    : $estadisticas['rendimiento_promedio'];
                                            ?>
                                            <?= number_format($prom, 1, ',', '.') ?> kg/ha
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de cosechas -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Lote</th>
                                        <th>Kilos</th>
                                        <th>Calidad</th>
                                        <th>Rendimiento</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cosechas as $cosecha): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($cosecha['fecha_cosecha'])) ?></td>
                                        <td><?= $cosecha['lote_nombre'] ?> (<?= $cosecha['codigo_lote'] ?>)</td>
                                        <td class="fw-bold"><?= number_format($cosecha['kilos_cosechados'], 0, ',', '.') ?> kg</td>
                                        <td>
                                            <span class="badge bg-<?= match($cosecha['calidad']) {
                                                'premium' => 'success',
                                                'especial' => 'info',
                                                'estandar' => 'primary',
                                                'comercial' => 'secondary'
                                            } ?>">
                                                <?= ucfirst($cosecha['calidad']) ?>
                                            </span>
                                        </td>
                                        <td><?= number_format($cosecha['rendimiento'], 1, ',', '.') ?> kg/ha</td>
                                        <td><?= $cosecha['observaciones'] ?: 'N/A' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script>
    // Calcular rendimiento estimado
    document.addEventListener('DOMContentLoaded', function() {
        const loteSelect = document.querySelector('select[name="lote_id"]');
        const kilosInput = document.querySelector('input[name="kilos_cosechados"]');
        const rendimientoInput = document.getElementById('rendimientoEstimado');

        // Esta función se completaría con datos reales del lote
        function calcularRendimiento() {
            const kilos = parseFloat(kilosInput.value) || 0;
            // En una implementación real, aquí se obtendría el área del lote seleccionado
            const area = 1.0; // Valor por defecto
            const rendimiento = kilos / area;
            rendimientoInput.value = rendimiento.toFixed(1) + ' kg/ha';
        }

        kilosInput.addEventListener('input', calcularRendimiento);
        loteSelect.addEventListener('change', function() {
            // Aquí se cargaría el área real del lote seleccionado
            calcularRendimiento();
        });
    });
    </script>
</body>
</html>
