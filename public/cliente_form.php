<?php
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/ClientesController.php';

$auth = new AuthController();
$usuario = $auth->checkAuth();

$clientesController = new ClientesController();
$cliente = [];

// Si hay ID, es edición
if (isset($_GET['id'])) {
    $cliente = $clientesController->obtenerConHistorial($_GET['id'])['cliente'];
    $titulo = 'Editar Cliente';
    $action = 'actualizar';
} else {
    $titulo = 'Nuevo Cliente';
    $action = 'crear';
}

// Manejar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'tipo_entidad' => $_POST['tipo_entidad'],
        'tipo_identificacion' => $_POST['tipo_identificacion'] ?? '',
        'numero_identificacion' => $_POST['numero_identificacion'],
        'nombres' => $_POST['nombres'] ?? '',
        'apellidos' => $_POST['apellidos'] ?? '',
        'razon_social' => $_POST['razon_social'] ?? '',
        'telefono' => $_POST['telefono'],
        'email' => $_POST['email'],
        'direccion' => $_POST['direccion'],
        'municipio' => $_POST['municipio']
    ];

    if ($action === 'crear') {
        if ($clientesController->crear($data)) {
            $_SESSION['success'] = 'Cliente creado exitosamente';
            header('Location: clientes.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error al crear cliente';
        }
    } else {
        if ($clientesController->actualizar($_GET['id'], $data)) {
            $_SESSION['success'] = 'Cliente actualizado exitosamente';
            header('Location: clientes.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error al actualizar cliente';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> - Finca Cafetera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include __DIR__ . '/app/views/components/header.php'; ?>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0"><?= $titulo ?></h2>
                    </div>
                    <div class="card-body">
                        <!-- Mensajes de alerta -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="cliente_form.php<?= isset($_GET['id']) ? '?id=' . $_GET['id'] : '' ?>">
                            <?php include(__DIR__ . '/app/views/clientes/form.php');
?>
                            
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success me-2">
                                        💾 <?= $action === 'crear' ? 'Crear Cliente' : 'Actualizar Cliente' ?>
                                    </button>
                                    <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('tipoEntidad').addEventListener('change', function() {
    const tipo = this.value;

    const camposPersona = document.querySelectorAll('#camposPersona input, #camposPersona select');
    const camposEmpresa = document.querySelectorAll('#camposEmpresa input, #camposEmpresa select');

    // Deshabilitar todos primero
    camposPersona.forEach(f => f.disabled = true);
    camposEmpresa.forEach(f => f.disabled = true);

    if (tipo === 'persona') {
        camposPersona.forEach(f => f.disabled = false);
    } else if (tipo === 'empresa') {
        camposEmpresa.forEach(f => f.disabled = false);
    }
});
</script>
</body>
</html>