<?php
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/IngresosController.php';

$auth = new AuthController();
$usuario = $auth->checkAuth();

$ingresosController = new IngresosController();
$ingresos = $ingresosController->index();
$clientes = $ingresosController->obtenerClientes();

// Obtener ingresos del mes CORREGIDO
$ingresosMes = $ingresosController->obtenerIngresosMes();

// Manejar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'crear') {
        $data = [
            'tipo' => $_POST['tipo'],
            'fecha_ingreso' => $_POST['fecha_ingreso'],
            'descripcion' => $_POST['descripcion'],
            'monto' => $_POST['monto'],
            'cliente_id' => $_POST['cliente_id'] ?: null
        ];
        
        if ($ingresosController->crear($data)) {
            $_SESSION['success'] = 'Ingreso registrado exitosamente';
            header('Location: ingresos.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error al registrar ingreso';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ingresos - Finca Cafetera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php 
    $titulo = 'Gestión de Ingresos';
    include '../app/views/components/header.php'; 
    ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">💰 Gestión de Ingresos</h2>
                    </div>
                    <div class="card-body">
                        <!-- Formulario de ingreso -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Registrar Nuevo Ingreso</h5>
                                        <form method="POST" action="ingresos.php">
                                            <input type="hidden" name="action" value="crear">
                                            
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tipo de Ingreso *</label>
                                                        <select class="form-select" name="tipo" required>
                                                            <option value="">Seleccionar tipo</option>
                                                            <option value="venta_cafe">Venta de Café</option>
                                                            <option value="subproductos">Subproductos</option>
                                                            <option value="alquiler">Alquiler</option>
                                                            <option value="otros">Otros</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Fecha *</label>
                                                        <input type="date" class="form-control" name="fecha_ingreso" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Cliente</label>
                                                        <select class="form-select" name="cliente_id">
                                                            <option value="">Seleccionar cliente...</option>
                                                            <?php foreach ($clientes as $cliente): ?>
                                                            <option value="<?= $cliente['id'] ?>">
                                                                <?= $cliente['nombres'] ? $cliente['nombres'] . ' ' . $cliente['apellidos'] : $cliente['razon_social'] ?>
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
                                                <textarea class="form-control" name="descripcion" rows="2" required placeholder="Descripción del ingreso..."></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-success">Registrar Ingreso</button>
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
                                <div class="card text-white bg-primary">
                                    <div class="card-body">
                                        <h5 class="card-title">Ingresos del Mes</h5>
                                        <h2 class="card-text">
                                            $<?= number_format($ingresosMes, 0, ',', '.') ?>
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de ingresos -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Cliente</th>
                                        <th>Monto</th>
                                        <th>Registrado por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($ingresos)): ?>
                                        <?php foreach ($ingresos as $ingreso): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($ingreso['fecha_ingreso'])) ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= match($ingreso['tipo']) {
                                                        'venta_cafe' => 'Venta Café',
                                                        'subproductos' => 'Subproductos',
                                                        'alquiler' => 'Alquiler',
                                                        'otros' => 'Otros',
                                                        default => $ingreso['tipo']
                                                    } ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($ingreso['descripcion']) ?></td>
                                            <td>
                                                <?php if ($ingreso['cliente_nombre'] || $ingreso['cliente_razon_social']): ?>
                                                    <?= $ingreso['cliente_nombre'] ? 
                                                        $ingreso['cliente_nombre'] . ' ' . $ingreso['cliente_apellidos'] : 
                                                        $ingreso['cliente_razon_social'] ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-success fw-bold">
                                                $<?= number_format($ingreso['monto'], 0, ',', '.') ?>
                                            </td>
                                            <td>Usuario #<?= $ingreso['usuario_id'] ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No hay ingresos registrados
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