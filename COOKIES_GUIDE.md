# Guía de Cookies Seguras - Finca Cafetera

## Descripción General

Se ha implementado un sistema completo de gestión segura de cookies en la aplicación. Las cookies se utilizan para mantener la sesión del usuario y proporcionar funcionalidad de "Recuérdame".

## Características de Seguridad

### Atributos Seguros de Cookies

Todas las cookies están configuradas con las siguientes opciones de seguridad:

- **Secure**: Solo se transmiten por HTTPS
- **HttpOnly**: No accesibles desde JavaScript (previene XSS)
- **SameSite=Strict**: Protección contra CSRF (requiere same-site origin)
- **Path=/**: Disponible en todo el sitio
- **Domain**: Dominio actual automáticamente

### Configuración de Sesión

Las cookies de sesión se configuran automáticamente en cada petición:

```php
SecurityManager::configureSessionCookies();
```

Opciones:

- **lifetime**: 0 (sesión del navegador - se elimina al cerrar)
- **secure**: true (solo HTTPS)
- **httponly**: true (no accesible desde JS)
- **samesite**: 'Strict' (protección CSRF)

## Métodos Disponibles

### Crear Cookie

```php
SecurityManager::setCookie($name, $value, $expire = 0, $httponly = true, $secure = true);
```

Parámetros:

- `$name`: Nombre de la cookie (solo alfanuméricos, guiones y guiones bajos)
- `$value`: Valor (se encripta automáticamente)
- `$expire`: Segundos desde ahora (0 = sesión del navegador)
- `$httponly`: No accesible desde JavaScript (recomendado: true)
- `$secure`: Solo HTTPS (recomendado: true)

**Ejemplo:**

```php
// Cookie de sesión (se elimina al cerrar navegador)
SecurityManager::setCookie('preferencias', 'tema_oscuro');

// Cookie por 7 días
SecurityManager::setCookie('idioma', 'es', 7 * 24 * 60 * 60);
```

### Obtener Cookie

```php
$valor = SecurityManager::getCookie($name);
```

**Ejemplo:**

```php
$preferencias = SecurityManager::getCookie('preferencias');
if ($preferencias) {
    echo "Preferencias: " . SecurityManager::escapeHTML($preferencias);
}
```

### Verificar Cookie

```php
if (SecurityManager::hasCookie('nombre')) {
    // La cookie existe
}
```

### Eliminar Cookie

```php
SecurityManager::deleteCookie($name);
```

## "Remember Me" (Recuérdame)

### Crear Cookie de Recuerdo

```php
SecurityManager::setRememberMeCookie($userId, $token, $days = 30);
```

Parámetros:

- `$userId`: ID del usuario
- `$token`: Token único y seguro
- `$days`: Número de días para recordar (por defecto: 30)

**Ejemplo en AuthController:**

```php
if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
    $rememberToken = bin2hex(random_bytes(32));
    SecurityManager::setRememberMeCookie($usuario['id'], $rememberToken, 30);
}
```

### Obtener Datos de Remember Me

```php
$rememberData = SecurityManager::getRememberMeCookie();

if ($rememberData) {
    // $rememberData contiene:
    // [
    //     'user_id' => (int),
    //     'token' => (string),
    //     'created' => (timestamp)
    // ]
}
```

### Validar Cookie de Remember Me

```php
$rememberData = SecurityManager::getRememberMeCookie();
$es_valida = SecurityManager::validateRememberMeCookie($rememberData, $storedToken);
```

La validación verifica:

- Que el token coincida
- Que no sea más antiguo de 90 días

### Eliminar Remember Me

```php
SecurityManager::deleteRememberMeCookie();
```

Se hace automáticamente en logout.

## Flujo de Autenticación

### 1. Login Inicial

```
Usuario ingresa credenciales
        ↓
Validar CSRF token
        ↓
Validar credenciales
        ↓
Crear sesión ($_SESSION['usuario'])
        ↓
Si "Recuérdame" está marcado:
    - Generar token único
    - Guardar en cookie con opciones seguras
        ↓
Redirigir al dashboard
```

### 2. Verificación de Autenticación (checkAuth)

```
¿Hay sesión activa?
    ✓ Sí → Retornar usuario
    ✗ No → Verificar "Remember Me" cookie
          ↓
          ¿Cookie válida?
              ✓ Sí → Restaurar sesión
              ✗ No → Redirigir a login
```

### 3. Logout

```
Eliminar "Remember Me" cookie
        ↓
Destruir sesión
        ↓
Redirigir a login
```

## Uso en Formularios

### Formulario de Login

El formulario incluye automáticamente:

```html
<form method="POST" action="...">
  <input type="hidden" name="_csrf_token" value="..." />

  <input type="text" name="username" required />
  <input type="password" name="password" required />

  <input type="checkbox" name="remember_me" />
  <label>Recuérdame por 30 días</label>

  <button type="submit">Iniciar Sesión</button>
</form>
```

## Mejores Prácticas

### 1. Siempre Usar Cookies Seguras

```php
// ✅ CORRECTO
SecurityManager::setCookie('mi_cookie', $valor, 0, true, true);

// ❌ INCORRECTO - sin HttpOnly
setcookie('mi_cookie', $valor);
```

### 2. Encripción Automática

Los valores se encriptan automáticamente:

```php
SecurityManager::setCookie('datos_sensibles', $valor);
// El valor se guarda encriptado en la cookie
// Se desencripta automáticamente al obtenerlo
```

### 3. Validar Siempre

```php
if (SecurityManager::hasCookie('token')) {
    $token = SecurityManager::getCookie('token');
    // Validar y usar
}
```

### 4. Limpiar en Logout

```php
SecurityManager::deleteRememberMeCookie();
session_destroy();
```

### 5. No Usar Cookies para Datos Sensibles sin Encriptación

```php
// ❌ RIESGOSO - incluso con encriptación
SecurityManager::setCookie('contraseña', $password);

// ✅ SEGURO - usar solo identificadores
SecurityManager::setCookie('user_id', $userId);
```

## Información de Cookie

### Obtener Información

```php
$info = SecurityManager::getCookieInfo('nombre');

// Retorna:
// [
//     'name' => 'nombre',
//     'value' => 'valor_desencriptado',
//     'exists' => true
// ]
```

## Limpiar Todas las Cookies

```php
SecurityManager::clearAllCookies();
```

Esta función elimina todas las cookies de la aplicación.

## Tiempos de Expiración Recomendados

| Tipo                 | Tiempo  | Uso                        |
| -------------------- | ------- | -------------------------- |
| Sesión del navegador | 0       | Datos temporales, tokens   |
| Corta                | 1 hora  | Preferencias, estado       |
| Normal               | 7 días  | Remember me, recordatorios |
| Larga                | 30 días | Autenticación recordada    |

## Estructura de Cookie Remember Me

```json
{
  "user_id": 123,
  "token": "abc123def456...",
  "created": 1700000000
}
```

- **user_id**: Identificador único del usuario
- **token**: Token criptográfico para validación
- **created**: Timestamp de creación para validar antigüedad

## Debugging

### Ver Cookies Enviadas

```php
// En el navegador
// F12 → Application → Cookies
```

### Verificar Atributos

```php
// En PHP
echo SecurityManager::getCookieInfo('nombre_cookie')['value'];
```

### Log de Cookies

```php
error_log("Cookies actuales: " . json_encode($_COOKIE));
```

## Compatibilidad

- PHP 7.3+
- Todos los navegadores modernos
- Support para SameSite: Chrome 51+, Firefox 60+, Safari 13+

## Ejemplo Completo: Sistema Remember Me

### 1. Login Form (login.php)

```html
<form method="POST" action="auth.php">
  <input type="text" name="username" required />
  <input type="password" name="password" required />
  <input type="checkbox" name="remember_me" />
  <label>Recuérdame</label>
  <button type="submit">Entrar</button>
</form>
```

### 2. Controlador (AuthController.php)

```php
// En login()
if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
    $token = bin2hex(random_bytes(32));
    SecurityManager::setRememberMeCookie($usuario['id'], $token, 30);

    // Opcional: Guardar token en DB para validación adicional
    // $this->usuarioModel->saveRememberToken($usuario['id'], $token);
}

// En logout()
SecurityManager::deleteRememberMeCookie();
session_destroy();
```

### 3. Verificación (checkAuth)

```php
if (!isset($_SESSION['usuario'])) {
    $rememberData = SecurityManager::getRememberMeCookie();

    if ($rememberData) {
        $usuario = $this->usuarioModel->find($rememberData['user_id']);

        if ($usuario) {
            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'username' => $usuario['username'],
                'nombre_completo' => $usuario['nombre_completo'],
                'rol' => $usuario['rol']
            ];
            return $_SESSION['usuario'];
        }
    }

    header('Location: /login.php');
    exit;
}
```

## Conclusión

El sistema de cookies implementado:

- ✅ Protege contra XSS (HttpOnly)
- ✅ Protege contra CSRF (SameSite)
- ✅ Solo se transmite por HTTPS (Secure)
- ✅ Encripta valores automáticamente
- ✅ Proporciona funcionalidad "Recuérdame"
- ✅ Valida expiración de tokens

Usar para mejorar la experiencia del usuario manteniendo la seguridad.
