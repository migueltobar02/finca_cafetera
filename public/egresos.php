<?php
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . 'app/controllers/EgresosController.php';

$auth = new AuthController();
$usuario = $auth->checkAuth();

$egresosController = new EgresosController();
$egresos = $egresosController->index();
$proveedores = $egresosController->obtenerProveedores();

// Obtener egresos del mes CORREGIDO
$egresosMes = $egresosController->obtenerEgresosMes();

// Manejar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'crear') {
        $data = [
            'tipo' => $_POST['tipo'],
            'fecha_egreso' => $_POST['fecha_egreso'],
            'descripcion' => $_POST['descripcion'],
            'monto' => $_POST['monto'],
            'proveedor_id' => $_POST['proveedor_id'] ?: null
        ];
        
        if ($egresosController->crear($data)) {
            $_SESSION['success'] = 'Egreso registrado exitosamente';
            header('Location: egresos.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error al registrar egreso';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Egresos - Finca Cafetera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php 
    $titulo = 'Gestión de Egresos';
    include __DIR__ . '/app/views/components/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">💸 Gestión de Egresos</h2>
                    </div>
                    <div class="card-body">
                        <!-- Formulario de egreso -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Registrar Nuevo Egreso</h5>
                                        <form method="POST" action="egresos.php">
                                            <input type="hidden" name="action" value="crear">
                                            
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tipo de Egreso *</label>
                                                        <select class="form-select" name="tipo" required>
                                                            <option value="">Seleccionar tipo</option>
                                                            <option value="insumos">Insumos</option>
                                                            <option value="salarios">Salarios</option>
                                                            <option value="mantenimiento">Mantenimiento</option>
                                                            <option value="servicios">Servicios</option>
                                                            <option value="otros">Otros</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Fecha *</label>
                                                        <input type="date" class="form-control" name="fecha_egreso" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Proveedor</label>
                                                        <select class="form-select" name="proveedor_id">
                                                            <option value="">Seleccionar proveedor...</option>
                                                            <?php foreach ($proveedores as $proveedor): ?>
                                                            <option value="<?= $proveedor['id'] ?>">
                                                                <?= $proveedor['razon_social'] ?>
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Monto *</label>
                                                        <input type="number" class="form-control" name="monto" step="0.01" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Descripción *</label>
                                                <textarea class="form-control" name="descripcion" rows="2" required placeholder="Descripción del egreso..."></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-success">Registrar Egreso</button>
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

                        <!-- Estadísticas rápidas -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card text-white bg-danger">
                                    <div class="card-body">
                                        <h5 class="card-title">Egresos del Mes</h5>
                                        <h2 class="card-text">
                                            $<?= number_format($egresosMes, 0, ',', '.') ?>
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de egresos -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Proveedor</th>
                                        <th>Monto</th>
                                        <th>Registrado por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($egresos)): ?>
                                        <?php foreach ($egresos as $egreso): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($egreso['fecha_egreso'])) ?></td>
                                            <td>
                                                <span class="badge bg-warning">
                                                    <?= match($egreso['tipo']) {
                                                        'insumos' => 'Insumos',
                                                        'salarios' => 'Salarios',
                                                        'mantenimiento' => 'Mantenimiento',
                                                        'servicios' => 'Servicios',
                                                        'otros' => 'Otros',
                                                        default => $egreso['tipo']
                                                    } ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($egreso['descripcion']) ?></td>
                                            <td>
                                                <?php if ($egreso['proveedor_nombre']): ?>
                                                    <?= $egreso['proveedor_nombre'] ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-danger fw-bold">
                                                $<?= number_format($egreso['monto'], 0, ',', '.') ?>
                                            </td>
                                            <td>Usuario #<?= $egreso['usuario_id'] ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No hay egresos registrados
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>