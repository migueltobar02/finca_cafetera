<?php
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/ClientesController.php';

$auth = new AuthController();
$usuario = $auth->checkAuth();

$clientesController = new ClientesController();
$clientes = $clientesController->index();
$clientesFrecuentes = $clientesController->getClientesFrecuentes();

// Manejar búsqueda
$terminoBusqueda = '';
if (isset($_GET['buscar']) && !empty($_GET['termino'])) {
    $terminoBusqueda = $_GET['termino'];
    $clientes = $clientesController->buscar($terminoBusqueda);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Finca Cafetera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include '../app/views/components/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">👥 Gestión de Clientes</h2>
                        <a href="cliente_form.php" class="btn btn-primary">+ Nuevo Cliente</a>
                    </div>
                    <div class="card-body">
                        <!-- Barra de búsqueda -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <form method="GET" action="clientes.php" class="d-flex">
                                    <input type="hidden" name="buscar" value="1">
                                    <input type="text" class="form-control me-2" name="termino" 
                                           placeholder="Buscar por nombre, documento o razón social..." 
                                           value="<?= htmlspecialchars($terminoBusqueda) ?>">
                                    <button type="submit" class="btn btn-outline-primary">Buscar</button>
                                    <?php if ($terminoBusqueda): ?>
                                    <a href="clientes.php" class="btn btn-outline-secondary ms-2">Limpiar</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>

                        <!-- Mensajes de alerta -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>

                        <!-- Clientes frecuentes -->
                        <?php if (!$terminoBusqueda && !empty($clientesFrecuentes)): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">⭐ Clientes Frecuentes</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Cliente</th>
                                                        <th>Documento</th>
                                                        <th>Total Compras</th>
                                                        <th>Monto Total</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($clientesFrecuentes as $cliente): ?>
                                                    <tr>
                                                        <td>
                                                            <strong>
                                                                <?= $cliente['nombres'] ? 
                                                                    $cliente['nombres'] . ' ' . $cliente['apellidos'] : 
                                                                    $cliente['razon_social'] ?>
                                                            </strong>
                                                        </td>
                                                        <td><?= $cliente['tipo_identificacion'] ?>: <?= $cliente['numero_identificacion'] ?></td>
                                                        <td><?= $cliente['total_compras'] ?> compras</td>
                                                        <td class="fw-bold text-success">$<?= number_format($cliente['monto_total'], 0, ',', '.') ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-info" onclick="verHistorial(<?= $cliente['id'] ?>)">
                                                                Historial
                                                            </button>
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
                        <?php endif; ?>

                        <!-- Tabla de clientes -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Documento</th>
                                        <th>Nombre / Razón Social</th>
                                        <th>Contacto</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?= $cliente['tipo_entidad'] == 'empresa' ? 'info' : 'primary' ?>">
                                                <?= ucfirst($cliente['tipo_entidad']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= $cliente['tipo_identificacion'] ?>:</small><br>
                                            <?= $cliente['numero_identificacion'] ?>
                                        </td>
                                        <td>
                                            <strong>
                                                <?= $cliente['nombres'] ? 
                                                    $cliente['nombres'] . ' ' . $cliente['apellidos'] : 
                                                    $cliente['razon_social'] ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php if ($cliente['telefono']): ?>
                                            <div>📞 <?= $cliente['telefono'] ?></div>
                                            <?php endif; ?>
                                            <?php if ($cliente['email']): ?>
                                            <div>✉️ <?= $cliente['email'] ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $cliente['estado'] == 'activo' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($cliente['estado']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-warning" onclick="editarCliente(<?= $cliente['id'] ?>)">
                                                    Editar
                                                </button>
                                                <button class="btn btn-info" onclick="verDetalles(<?= $cliente['id'] ?>)">
                                                    Detalles
                                                </button>
                                                <?php if ($cliente['estado'] == 'activo'): ?>
                                                <button class="btn btn-danger" onclick="eliminarCliente(<?= $cliente['id'] ?>)">
                                                    Desactivar
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (empty($clientes)): ?>
                        <div class="text-center py-4">
                            <h4 class="text-muted">No se encontraron clientes</h4>
                            <p class="text-muted">
                                <?= $terminoBusqueda ? 
                                    'No hay resultados para "' . htmlspecialchars($terminoBusqueda) . '"' : 
                                    'No hay clientes registrados' ?>
                            </p>
                            <?php if (!$terminoBusqueda): ?>
                            <a href="cliente_form.php" class="btn btn-primary">Registrar Primer Cliente</a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script>
    function editarCliente(id) {
        window.location.href = 'cliente_form.php?id=' + id;
    }

    function verDetalles(id) {
        window.location.href = 'cliente_detalles.php?id=' + id;
    }

    function verHistorial(id) {
        window.location.href = 'cliente_detalles.php?id=' + id + '#historial';
    }

    function eliminarCliente(id) {
        if (confirm('¿Está seguro de que desea desactivar este cliente?')) {
            window.location.href = 'clientes.php?action=eliminar&id=' + id;
        }
    }
    </script>
</body>
</html>