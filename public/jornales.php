<?php
session_start();
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/JornalesController.php';
require_once __DIR__ . '/app/controllers/CosechasController.php';

$auth = new AuthController();
$usuario = $auth->checkAuth();

$jornalesController = new JornalesController();
$jornales = $jornalesController->index();
$empleados = $jornalesController->obtenerEmpleados();
$actividades = $jornalesController->obtenerActividades();

// Obtener cosechas (puede estar vacío)
$cosechasController = new CosechasController();
$cosechas = $cosechasController->index();

// Manejar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'crear') {
        $data = [
            'empleado_id' => $_POST['empleado_id'],
            'fecha_jornal' => $_POST['fecha_jornal'],
            'horas_trabajadas' => $_POST['horas_trabajadas'],
            'tarifa_hora' => $_POST['tarifa_hora'],
            'actividad_id' => $_POST['actividad_id'],
            'cosecha_id' => $_POST['cosecha_id'] ?? null
        ];
        
        if ($jornalesController->crear($data)) {
            $_SESSION['success'] = 'Jornal registrado exitosamente';
            header('Location: jornales.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error al registrar jornal';
        }
    }
    
    if ($action === 'pagar') {
        $id = $_POST['id'] ?? null;
        if ($id && $jornalesController->marcarPagado($id)) {
            $_SESSION['success'] = 'Jornal marcado como pagado';
            header('Location: jornales.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Jornales - Finca Cafetera</title>
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
                        <h2 class="mb-0">⏱️ Gestión de Jornales</h2>
                    </div>
                    <div class="card-body">
                        <!-- Formulario de jornal -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Registrar Nuevo Jornal</h5>
                                        <form method="POST" action="jornales.php">
                                            <input type="hidden" name="action" value="crear">
                                            
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Empleado *</label>
                                                        <select class="form-select" name="empleado_id" required>
                                                            <option value="">Seleccionar empleado...</option>
                                                            <?php foreach ($empleados as $empleado): ?>
                                                            <option value="<?= $empleado['id'] ?>">
                                                                <?= $empleado['nombres'] ?> <?= $empleado['apellidos'] ?>
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Fecha *</label>
                                                        <input type="date" class="form-control" name="fecha_jornal" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Horas Trabajadas *</label>
                                                        <input type="number" class="form-control" name="horas_trabajadas" step="0.5" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tarifa por Hora *</label>
                                                        <input type="number" class="form-control" name="tarifa_hora" step="0.01" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Actividad *</label>
                                                        <select class="form-select" name="actividad_id" required>
                                                            <option value="">Seleccionar actividad...</option>
                                                            <?php foreach ($actividades as $actividad): ?>
                                                            <option value="<?= $actividad['id'] ?>">
                                                                <?= $actividad['nombre'] ?>
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Cosecha</label>
                                                        <select class="form-select" name="cosecha_id">
                                                            <option value="">Seleccionar cosecha...</option>
                                                            <?php foreach ($cosechas as $cosecha): ?>
                                                                <option value="<?= $cosecha['id'] ?>">
                                                                    <?= 'Cosecha ' . $cosecha['id'] . ' - ' . $cosecha['fecha_cosecha'] ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Total a Pagar</label>
                                                        <input type="text" class="form-control" readonly id="totalPagar">
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-success">Registrar Jornal</button>
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

                        <!-- Tabla de jornales -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Empleado</th>
                                        <th>Actividad</th>
                                        <th>Cosecha</th>
                                        <th>Horas</th>
                                        <th>Tarifa/Hora</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jornales as $jornal): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($jornal['fecha_jornal'])) ?></td>
                                        <td>Empleado #<?= $jornal['empleado_id'] ?></td>
                                        <td>Actividad #<?= $jornal['actividad_id'] ?></td>
                                        <td><?= $jornal['cosecha_id'] ?? 'N/A' ?></td>
                                        <td><?= $jornal['horas_trabajadas'] ?> hrs</td>
                                        <td>$<?= number_format($jornal['tarifa_hora'], 0, ',', '.') ?></td>
                                        <td class="fw-bold">$<?= number_format($jornal['total_pago'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $jornal['estado'] == 'pagado' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($jornal['estado']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($jornal['estado'] == 'pendiente'): ?>
                                            <button class="btn btn-sm btn-success" onclick="marcarPagado(<?= $jornal['id'] ?>)">
                                                Marcar como Pagado
                                            </button>
                                            <?php endif; ?>
                                        </td>
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
// Calcular total automáticamente
document.addEventListener('DOMContentLoaded', function() {
    const horasInput = document.querySelector('input[name="horas_trabajadas"]');
    const tarifaInput = document.querySelector('input[name="tarifa_hora"]');
    const totalInput = document.getElementById('totalPagar');

    function calcularTotal() {
        const horas = parseFloat(horasInput.value) || 0;
        const tarifa = parseFloat(tarifaInput.value) || 0;
        const total = horas * tarifa;
        totalInput.value = '$' + total.toLocaleString('es-CO');
    }

    horasInput.addEventListener('input', calcularTotal);
    tarifaInput.addEventListener('input', calcularTotal);
});

function marcarPagado(jornalId) {
    if (confirm('¿Está seguro de marcar este jornal como pagado?')) {
        window.location.href = 'jornales.php?action=pagar&id=' + jornalId;
    }
}
</script>
</body>
</html>
