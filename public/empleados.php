<?php
// Cargar el autoloader del proyecto (resuelve rutas desde la raíz del repo)
require_once __DIR__ . '/app/autoload.php';

$auth = new AuthController();
$usuario = $auth->checkAuth();

    $empleadosController = new EmpleadosController();
    $empleados = $empleadosController->index();

// Manejar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'crear') {
        $data = [
            'documento_identidad' => $_POST['documento_identidad'],
            'tipo_documento' => $_POST['tipo_documento'],
            'nombres' => $_POST['nombres'],
            'apellidos' => $_POST['apellidos'],
            'telefono' => $_POST['telefono'],
            'email' => $_POST['email'],
            'direccion' => $_POST['direccion'],
            'cargo' => $_POST['cargo'],
            'salario_base' => $_POST['salario_base'],
            'fecha_contratacion' => $_POST['fecha_contratacion']
        ];
        
        if ($empleadosController->crear($data)) {
            $_SESSION['success'] = 'Empleado creado exitosamente';
            header('Location: empleados.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error al crear empleado';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados - Finca Cafetera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../app/views/components/header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">👥 Gestión de Empleados</h2>
                    </div>
                    <div class="card-body">
                        <!-- Botón para agregar empleado -->
                        <div class="mb-4">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEmpleado">
                                + Agregar Empleado
                            </button>
                        </div>

                        <!-- Mensajes de alerta -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>

                        <!-- Tabla de empleados -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Documento</th>
                                        <th>Nombre Completo</th>
                                        <th>Cargo</th>
                                        <th>Teléfono</th>
                                        <th>Salario Base</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($empleados as $empleado): ?>
                                    <tr>
                                        <td><?= $empleado['tipo_documento'] ?>: <?= $empleado['documento_identidad'] ?></td>
                                        <td><?= $empleado['nombres'] ?> <?= $empleado['apellidos'] ?></td>
                                        <td><?= $empleado['cargo'] ?></td>
                                        <td><?= $empleado['telefono'] ?></td>
                                        <td>$<?= number_format($empleado['salario_base'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $empleado['estado'] == 'activo' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($empleado['estado']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editarEmpleado(<?= $empleado['id'] ?>)">
                                                Editar
                                            </button>
                                            <button class="btn btn-sm btn-info" onclick="verDetalles(<?= $empleado['id'] ?>)">
                                                Detalles
                                            </button>
                                            <?php if ($empleado['estado'] == 'activo'): ?>
                                            <button class="btn btn-sm btn-danger" onclick="eliminarEmpleado(<?= $empleado['id'] ?>)">
                                                Desactivar
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

    <!-- Modal para agregar/editar empleado -->
    <div class="modal fade" id="modalEmpleado" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Agregar Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="empleados.php">
                    <input type="hidden" name="action" value="crear" id="formAction">
                    <input type="hidden" name="empleado_id" id="empleadoId">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tipo Documento *</label>
                                    <select class="form-select" name="tipo_documento" required>
                                        <option value="CC">Cédula de Ciudadanía</option>
                                        <option value="CE">Cédula de Extranjería</option>
                                        <option value="PAS">Pasaporte</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Número Documento *</label>
                                    <input type="text" class="form-control" name="documento_identidad" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nombres *</label>
                                    <input type="text" class="form-control" name="nombres" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Apellidos *</label>
                                    <input type="text" class="form-control" name="apellidos" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" name="telefono">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <textarea class="form-control" name="direccion" rows="2"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Cargo *</label>
                                    <input type="text" class="form-control" name="cargo" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Salario Base *</label>
                                    <input type="number" class="form-control" name="salario_base" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fecha de Contratación *</label>
                            <input type="date" class="form-control" name="fecha_contratacion" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script>
    function editarEmpleado(id) {
        // Aquí iría la lógica para cargar los datos del empleado
        alert('Funcionalidad de edición en desarrollo para empleado ID: ' + id);
    }

    function verDetalles(id) {
        window.location.href = 'empleado_detalles.php?id=' + id;
    }

    function eliminarEmpleado(id) {
        if (confirm('¿Está seguro de que desea desactivar este empleado?')) {
            window.location.href = 'empleados.php?action=eliminar&id=' + id;
        }
    }
    </script>
</body>
</html>