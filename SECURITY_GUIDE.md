# Guía de Seguridad - CSRF y XSS Protection

## Descripción General

Se ha implementado protección contra dos de los ataques web más comunes:

- **CSRF (Cross-Site Request Forgery)**: Previene solicitudes no autorizadas desde otros sitios
- **XSS (Cross-Site Scripting)**: Previene inyección de código malicioso en el navegador

## Clase SecurityManager

La clase `SecurityManager` en `app/models/SecurityManager.php` centraliza todas las funciones de seguridad.

### CSRF Protection (Cross-Site Request Forgery)

#### Generar Token CSRF

```php
$token = SecurityManager::generateCSRFToken();
```

Genera un token único y seguro. Se almacena en sesión con tiempo de expiración (1 hora por defecto).

#### Obtener Token CSRF

```php
$token = SecurityManager::getCSRFToken();
```

Obtiene el token actual de la sesión o genera uno nuevo si no existe.

#### Validar Token CSRF

```php
$es_valido = SecurityManager::validateCSRFToken($_POST['_csrf_token']);
```

Valida un token específico. Retorna `false` si:

- El token no existe
- El token no coincide
- El token ha expirado (> 1 hora)

#### Validar Token desde Solicitud

```php
if (!SecurityManager::validateCSRFFromRequest()) {
    die('CSRF token inválido');
}
```

Valida automáticamente desde `$_POST` o `$_GET`.

### Uso en Formularios

#### En HTML

```html
<form method="POST" action="procesar.php">
  <?= csrfToken() ?>
  <!-- Helper function en header.php -->

  <input type="text" name="nombre" required />
  <button type="submit">Enviar</button>
</form>
```

O manualmente:

```html
<form method="POST" action="procesar.php">
  <input
    type="hidden"
    name="_csrf_token"
    value="<?= SecurityManager::escapeAttribute(SecurityManager::getCSRFToken()) ?>"
  />

  <input type="text" name="nombre" required />
  <button type="submit">Enviar</button>
</form>
```

#### En Controladores (POST)

```php
public function procesar() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar CSRF PRIMERO
        if (!SecurityManager::validateCSRFFromRequest()) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            return false;
        }

        // Luego procesar
        $datos = $_POST;
        // ...
    }
}
```

## XSS Protection (Cross-Site Scripting)

### Sanitizar Entrada

```php
// Texto simple
$nombre = SecurityManager::sanitizeInput($_POST['nombre'], 'text');

// Email
$email = SecurityManager::sanitizeInput($_POST['email'], 'email');

// URL
$url = SecurityManager::sanitizeInput($_POST['url'], 'url');

// Número entero
$id = SecurityManager::sanitizeInput($_POST['id'], 'int');

// Número decimal
$precio = SecurityManager::sanitizeInput($_POST['precio'], 'float');

// HTML limitado (whitelist)
$contenido = SecurityManager::sanitizeInput($_POST['contenido'], 'html');
```

### Escapar para Salida HTML

```php
// Escaper básico para HTML
echo SecurityManager::escapeHTML($variable);

// En atributos HTML
<img src="<?= SecurityManager::escapeAttribute($url) ?>">

// En JSON
header('Content-Type: application/json');
echo SecurityManager::escapeJSON($datos);
```

### Sanitizar HTML Específico

```php
// Permite tags seguros, remueve scripts y eventos
$html_limpio = SecurityManager::sanitizeHTML($html_usuario);

// Tags permitidos: p, br, strong, b, em, i, u, ul, ol, li, h1-h6, blockquote, a
```

## Headers de Seguridad

Se establecen automáticamente en cada carga de página:

```php
SecurityManager::setSecurityHeaders();
```

Headers incluidos:

- **X-Frame-Options**: `SAMEORIGIN` - Previene clickjacking
- **X-Content-Type-Options**: `nosniff` - Previene MIME sniffing
- **X-XSS-Protection**: `1; mode=block` - XSS protection en navegadores antiguos
- **Content-Security-Policy**: Restricciones de recursos
- **Referrer-Policy**: Control de información referrer
- **Permissions-Policy**: Control de permisos del navegador

## Validaciones Adicionales

### Validar Longitud

```php
if (!SecurityManager::validateLength($_POST['nombre'], 255, 1)) {
    echo "El nombre debe tener entre 1 y 255 caracteres";
}
```

### Validar JSON

```php
if (SecurityManager::validateJSON($_POST['datos'])) {
    $datos = json_decode($_POST['datos'], true);
}
```

## Mejores Prácticas

### 1. Siempre Validar CSRF en POST

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SecurityManager::validateCSRFFromRequest()) {
        $_SESSION['error'] = 'Token inválido';
        return;
    }
    // Procesar...
}
```

### 2. Sanitizar Entrada según Tipo

```php
$nombre = SecurityManager::sanitizeInput($_POST['nombre'], 'text');
$email = SecurityManager::sanitizeInput($_POST['email'], 'email');
$edad = SecurityManager::sanitizeInput($_POST['edad'], 'int');
```

### 3. Escapar al Mostrar

```html
<!-- En HTML -->
<p><?= SecurityManager::escapeHTML($variable) ?></p>

<!-- En atributos -->
<a href="<?= SecurityManager::escapeAttribute($url) ?>">Link</a>

<!-- En JSON -->
<?php echo SecurityManager::escapeJSON($datos); ?>
```

### 4. Nunca Permitir HTML del Usuario

```php
// ❌ MALO - permite cualquier HTML
echo $_POST['contenido'];

// ✅ BUENO - sanitiza HTML peligroso
echo SecurityManager::sanitizeHTML($_POST['contenido']);
```

### 5. Usar Prepared Statements

```php
// ✅ CORRECTO - protegido contra SQL injection
$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ?");
$stmt->execute([$username]);

// ❌ INCORRECTO - vulnerable a SQL injection
$result = $db->query("SELECT * FROM usuarios WHERE username = '$username'");
```

## Ejemplo Completo: Crear Cliente

### Formulario (cliente_form.php)

```html
<?php include '../app/views/components/header.php'; ?>

<form method="POST" action="clientes.php">
  <?= csrfToken() ?>
  <input type="hidden" name="action" value="crear" />

  <div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input
      type="text"
      class="form-control"
      id="nombre"
      name="nombre"
      required
    />
  </div>

  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email" required />
  </div>

  <button type="submit" class="btn btn-primary">Guardar</button>
</form>
```

### Controlador (ClientesController.php)

```php
public function crear($data) {
    // Validar CSRF
    if (!SecurityManager::validateCSRFFromRequest()) {
        throw new Exception('Token CSRF inválido');
    }

    // Sanitizar entrada
    $datos = [
        'nombre' => SecurityManager::sanitizeInput($data['nombre'], 'text'),
        'email' => SecurityManager::sanitizeInput($data['email'], 'email'),
    ];

    // Validar
    if (empty($datos['nombre']) || empty($datos['email'])) {
        throw new Exception('Campos requeridos');
    }

    // Guardar
    return $this->clienteModel->create($datos);
}
```

### Vista (clientes.php)

```php
<?php
$clientesController = new ClientesController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'crear') {
    try {
        $clientesController->crear($_POST);
        $_SESSION['success'] = 'Cliente creado correctamente';
    } catch (Exception $e) {
        $_SESSION['error'] = SecurityManager::escapeHTML($e->getMessage());
    }
}

$clientes = $clientesController->index();
?>

<!-- Mostrar clientes con HTML escapado -->
<?php foreach ($clientes as $cliente): ?>
    <tr>
        <td><?= SecurityManager::escapeHTML($cliente['nombre']) ?></td>
        <td><?= SecurityManager::escapeHTML($cliente['email']) ?></td>
    </tr>
<?php endforeach; ?>
```

## Regenerar Token

Para máxima seguridad después de validación:

```php
SecurityManager::validateCSRFFromRequest();
SecurityManager::regenerateCSRFToken(); // Nuevo token
```

## Desactivar Seguridad (NO RECOMENDADO)

Para debugging (nunca en producción):

```php
// Saltarse validación CSRF
if (SecurityManager::validateCSRFFromRequest() || getenv('DEBUG_MODE')) {
    // Procesar
}
```

## Conclusión

La implementación de CSRF y XSS protection:

- ✅ Previene ataques CSRF mediante tokens únicos
- ✅ Previene XSS mediante sanitización y escapado
- ✅ Establece headers de seguridad HTTP
- ✅ Valida y tipifica entrada
- ✅ Escapa salida según contexto

Siempre validar CSRF en formularios y sanitizar entrada de usuarios.
