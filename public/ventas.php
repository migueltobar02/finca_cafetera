<?php
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/VentasController.php';

$auth = new AuthController();
$usuario = $auth->checkAuth();

$ventasController = new VentasController();
$ventas = $ventasController->index();
$clientes = $ventasController->obtenerClientes();

// Obtener estadísticas con manejo de errores
try {
    $estadisticas = $ventasController->getEstadisticasVentas();
} catch (Exception $e) {
    $estadisticas = [
        'ventas_mes' => ['total_kilos' => 0, 'total_ventas' => 0],
        'top_clientes' => [],
        'distribucion_ventas' => []
    ];
    error_log("Error obteniendo estadísticas de ventas: " . $e->getMessage());
}

// Manejar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'crear') {
        $data = [
            'cliente_id' => $_POST['cliente_id'],
            'fecha_venta' => $_POST['fecha_venta'],
            'kilos_vendidos' => $_POST['kilos_vendidos'],
            'precio_kilo' => $_POST['precio_kilo'],
            'calidad' => $_POST['calidad'],
            'forma_pago' => $_POST['forma_pago']
        ];
        
        if ($ventasController->crear($data)) {
            $_SESSION['success'] = 'Venta registrada exitosamente';
            header('Location: ventas.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error al registrar venta';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Ventas - Finca Cafetera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php 
    $titulo = 'Registro de Ventas';
    include '../app/views/components/header.php'; 
    ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">🛒 Registro de Ventas</h2>
                    </div>
                    <div class="card-body">
                        <!-- Formulario de venta -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Registrar Nueva Venta</h5>
                                        <form method="POST" action="ventas.php">
                                            <input type="hidden" name="action" value="crear">
                                            
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Cliente *</label>
                                                        <select class="form-select" name="cliente_id" required>
                                                            <option value="">Seleccionar cliente...</option>
                                                            <?php foreach ($clientes as $cliente): ?>
                                                            <option value="<?= $cliente['id'] ?>">
                                                                <?= $cliente['nombres'] ? $cliente['nombres'] . ' ' . $cliente['apellidos'] : $cliente['razon_social'] ?>
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Fecha de Venta *</label>
                                                        <input type="date" class="form-control" name="fecha_venta" value="<?= date('Y-m-d') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Forma de Pago *</label>
                                                        <select class="form-select" name="forma_pago" required>
                                                            <option value="efectivo">Efectivo</option>
                                                            <option value="transferencia">Transferencia</option>
                                                            <option value="credito">Crédito</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Kilos Vendidos *</label>
                                                        <input type="number" class="form-control" name="kilos_vendidos" step="0.01" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Precio por Kilo *</label>
                                                        <input type="number" class="form-control" name="precio_kilo" step="0.01" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
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
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Total Venta</label>
                                                        <input type="text" class="form-control" readonly id="totalVenta">
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-success">Registrar Venta</button>
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
                                <div class="card text-white bg-primary">
                                    <div class="card-body">
                                        <h5 class="card-title">Ventas del Mes</h5>
                                        <h2 class="card-text">
                                            $<?= number_format($estadisticas['ventas_mes']['total_ventas'] ?? 0, 0, ',', '.') ?>
                                        </h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                        <h5 class="card-title">Kilos Vendidos</h5>
                                        <h2 class="card-text">
                                            <?= number_format($estadisticas['ventas_mes']['total_kilos'] ?? 0, 0, ',', '.') ?> kg
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Clientes -->
                        <?php if (!empty($estadisticas['top_clientes'])): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">🏆 Top Clientes del Mes</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Cliente</th>
                                                        <th>Total Compras</th>
                                                        <th>Kilos Comprados</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($estadisticas['top_clientes'] as $cliente): ?>
                                                    <tr>
                                                        <td>
                                                            <?= $cliente['nombres'] ? 
                                                                $cliente['nombres'] . ' ' . $cliente['apellidos'] : 
                                                                $cliente['razon_social'] ?>
                                                        </td>
                                                        <td class="fw-bold">$<?= number_format($cliente['total_compras_monto'] ?? 0, 0, ',', '.') ?></td>
                                                        <td><?= number_format($cliente['total_kilos'] ?? 0, 0, ',', '.') ?> kg</td>
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

                        <!-- Tabla de ventas -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Kilos</th>
                                        <th>Precio/kg</th>
                                        <th>Calidad</th>
                                        <th>Total</th>
                                        <th>Forma Pago</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($ventas)): ?>
                                        <?php foreach ($ventas as $venta): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($venta['fecha_venta'])) ?></td>
                                            <td>
                                                <?= $venta['cliente_nombres'] ? 
                                                    $venta['cliente_nombres'] . ' ' . $venta['cliente_apellidos'] : 
                                                    $venta['cliente_razon_social'] ?>
                                            </td>
                                            <td><?= number_format($venta['kilos_vendidos'], 0, ',', '.') ?> kg</td>
                                            <td>$<?= number_format($venta['precio_kilo'], 0, ',', '.') ?></td>
                                            <td>
                                                <span class="badge bg-<?= match($venta['calidad']) {
                                                    'premium' => 'success',
                                                    'especial' => 'info',
                                                    'estandar' => 'primary',
                                                    'comercial' => 'secondary',
                                                    default => 'secondary'
                                                } ?>">
                                                    <?= ucfirst($venta['calidad']) ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold">$<?= number_format($venta['total_venta'], 0, ',', '.') ?></td>
                                            <td>
                                                <span class="badge bg-<?= match($venta['forma_pago']) {
                                                    'efectivo' => 'success',
                                                    'transferencia' => 'info',
                                                    'credito' => 'warning',
                                                    default => 'secondary'
                                                } ?>">
                                                    <?= ucfirst($venta['forma_pago']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= ($venta['estado'] == 'pagada') ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($venta['estado']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($venta['estado'] == 'pendiente'): ?>
                                                <button class="btn btn-sm btn-success" onclick="marcarPagada(<?= $venta['id'] ?>)">
                                                    Marcar como Pagada
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                No hay ventas registradas
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
    <script>
    // Calcular total automáticamente
    document.addEventListener('DOMContentLoaded', function() {
        const kilosInput = document.querySelector('input[name="kilos_vendidos"]');
        const precioInput = document.querySelector('input[name="precio_kilo"]');
        const totalInput = document.getElementById('totalVenta');

        function calcularTotal() {
            const kilos = parseFloat(kilosInput.value) || 0;
            const precio = parseFloat(precioInput.value) || 0;
            const total = kilos * precio;
            totalInput.value = '$' + total.toLocaleString('es-CO');
        }

        kilosInput.addEventListener('input', calcularTotal);
        precioInput.addEventListener('input', calcularTotal);
    });

    function marcarPagada(ventaId) {
        if (confirm('¿Está seguro de marcar esta venta como pagada?')) {
            window.location.href = 'ventas.php?action=pagar&id=' + ventaId;
        }
    }
    </script>
</body>
</html>