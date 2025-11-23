<?php
require_once __DIR__ . '/app/controllers/ClientesController.php';

$controller = new ClientesController();

// Si es edición, obtener información
$cliente = null;
if (!empty($_GET['id'])) {
    $cliente = $controller->obtenerPorId($_GET['id']);
}

// Valores por defecto
$tipoEntidad     = $cliente['tipo_entidad'] ?? 'persona';
$numeroId        = $cliente['numero_identificacion'] ?? '';
$nombre          = $cliente['nombre'] ?? '';
$apellido        = $cliente['apellido'] ?? '';
$fechaNacimiento = $cliente['fecha_nacimiento'] ?? '';
$razonSocial     = $cliente['razon_social'] ?? '';
$fechaRegistro   = $cliente['fecha_registro'] ?? date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario Cliente</title>
</head>

<body>

<h2><?php echo $cliente ? "Editar Cliente" : "Crear Cliente"; ?></h2>

<form method="POST" action="cliente_guardar.php">

    <input type="hidden" name="id" value="<?php echo $cliente['id'] ?? ''; ?>">

    <!-- Tipo de entidad -->
    <label>Tipo de Entidad:</label>
    <select name="tipo_entidad" id="tipoEntidad" required>
        <option value="persona" <?php echo $tipoEntidad === 'persona' ? 'selected' : ''; ?>>Persona</option>
        <option value="empresa" <?php echo $tipoEntidad === 'empresa' ? 'selected' : ''; ?>>Empresa</option>
    </select>
    <br><br>

    <!-- Número de identificación -->
    <label>Número de Identificación:</label>
    <input type="text" name="numero_identificacion" value="<?php echo $numeroId; ?>" required>
    <br><br>

    <!-- CAMPOS PERSONA -->
    <div id="camposPersona">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo $nombre; ?>">
        <br><br>

        <label>Apellido:</label>
        <input type="text" name="apellido" value="<?php echo $apellido; ?>">
        <br><br>

        <label>Fecha de Nacimiento:</label>
        <input type="date" name="fecha_nacimiento" value="<?php echo $fechaNacimiento; ?>">
        <br><br>
    </div>

    <!-- CAMPOS EMPRESA -->
    <div id="camposEmpresa">
        <label>Razón Social:</label>
        <input type="text" name="razon_social" value="<?php echo $razonSocial; ?>">
        <br><br>

        <label>Fecha de Registro:</label>
        <input type="date" name="fecha_registro" value="<?php echo $fechaRegistro; ?>">
        <br><br>
    </div>

    <button type="submit">Guardar</button>
</form>

<!-- SCRIPT COMPLETO Y FUNCIONAL -->
<script>
document.getElementById('tipoEntidad').addEventListener('change', function() {
    const tipo = this.value;

    const camposPersona = document.querySelectorAll('#camposPersona input');
    const camposEmpresa = document.querySelectorAll('#camposEmpresa input');

    // Deshabilitar todos primero
    camposPersona.forEach(f => f.disabled = true);
    camposEmpresa.forEach(f => f.disabled = true);

    // Habilitar solo los que correspondan
    if (tipo === 'persona') {
        camposPersona.forEach(f => f.disabled = false);
    } else {
        camposEmpresa.forEach(f => f.disabled = false);
    }
});

// Ejecutar al cargar (IMPORTANTE)
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('tipoEntidad').dispatchEvent(new Event('change'));
});
</script>

</body>
</html>
