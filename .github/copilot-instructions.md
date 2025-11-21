# Copilot Instructions - Finca Cafetera

## Architecture Overview

**Finca Cafetera** es un sistema de gestión para una finca cafetera construido con **PHP vanilla** (sin frameworks) usando una arquitectura **MVC ligera** con separación clara entre controladores, modelos y vistas.

### Directory Structure

- **`app/autoload.php`** - Autocargador PSR-4 que resuelve clases desde `models/` y `controllers/`
- **`app/config/database.php`** - Configuración de conexión MySQL (estática, sin env file)
- **`app/models/`** - Lógica de negocio con patrón **Singleton para Database**
- **`app/controllers/`** - Manejo de peticiones y orquestación de modelos
- **`app/views/`** - Componentes reutilizables (solo `header.php` y formularios)
- **`public/`** - Puntos de entrada (entry points) que requieren autenticación
- **`database/`** - Schema SQL inicial

### Key Architectural Decisions

1. **Model Base Class** (`Model.php`):

   - Todos los modelos heredan de `Model` que proporciona CRUD genérico
   - Soporte automático para **borrado suave** (`soft delete`) con campo `estado = 'activo'|'inactivo'`
   - Métodos `query()` y `execute()` para consultas personalizadas
   - Instancia PDO obtenida via `Database::getInstance()->getConnection()`

2. **Database Singleton Pattern**:

   - `Database.php` implementa singleton con lazy initialization
   - Todas las conexiones usan la misma instancia de PDO
   - Conexión se establece en constructor privado al primer acceso

3. **Authentication Model**:

   - Session-based con `$_SESSION['usuario']` almacenando `[id, username, nombre_completo, rol]`
   - Verificación de autenticación en `AuthController::checkAuth()` redirige a login
   - Contraseñas hasheadas con `password_hash(PASSWORD_DEFAULT)`

4. **View Layer**:
   - Vistas son archivos PHP directamente en `public/` (no existe template engine)
   - Componente `header.php` incluido via `include` y usa `$usuario` de sesión
   - Formularios separados: `cliente_form.php`, `empleados/form.php`, etc.

## Development Workflows

### Adding a New CRUD Feature

1. **Create Model** (e.g., `app/models/Nuevo.php`):

   ```php
   class Nuevo extends Model {
       public function __construct() {
           parent::__construct('nombre_tabla');
       }
   }
   ```

2. **Create Controller** (e.g., `app/controllers/NuevoController.php`):

   - Instantiate model en constructor
   - Métodos: `index()`, `crear()`, `actualizar()`, `eliminar()`
   - Use `Database::getInstance()->getConnection()` para custom queries

3. **Create View** (e.g., `public/nuevo.php`):
   - Begin con auth check: `require_once '../app/controllers/AuthController.php'; $auth->checkAuth();`
   - Include header: `<?php include '../app/views/components/header.php'; ?>`
   - Use controller to fetch data: `$controller = new NuevoController(); $datos = $controller->index();`

### Database Queries

- **Simple CRUD**: Use inherited `getAll()`, `find($id)`, `create($data)`, `update($id, $data)`, `delete($id)`
- **Custom queries**: Call `$this->query($sql, $params)` from model or `Database::getInstance()->getConnection()->prepare()` from controller
- **Search**: See `ClientesController::buscar()` - construct SQL with LIKE and parameter binding

### Soft Delete Behavior

Models con `$softDelete = true` (default):

- `getAll()` y `find()` excluyen automáticamente registros con `estado = 'inactivo'`
- `delete()` hace UPDATE a `estado = 'inactivo'` en lugar de DELETE
- Para queries custom, **siempre filtrar**: `WHERE ... AND estado = 'activo'`

## Code Patterns & Conventions

### Controllers

- **No output directo** - retornan datos que las vistas renderizan
- Siempre instanciar modelos en constructor
- Métodos públicos para acciones principales
- Manejo de excepciones PDO delegado a Database (die en constructor)

### Models

- Heredar de `Model` base
- Constructor llama `parent::__construct('tabla_name')`
- Custom queries usan `$this->db->prepare()` directamente
- Validación de datos **delegada a controladores** (modelo solo persiste)

### Views (public/\*.php)

- Punto de entrada: requiere autoloader, instancia controlador, check auth
- Incluye header reutilizable para navegación
- Variables pasadas implícitamente: `$usuario` (sesión), `$datos` (controlador)
- Errores/éxito en `$_SESSION['error']` y `$_SESSION['success']`

### Database Config

- Archivo `app/config/database.php` con constantes de clase estática
- **No env file** - hardcoded credentials (desarrollo local XAMPP)
- HOST: `localhost`, DB: `finca_cafetera`, USER: `root`, PASS: `` (vacía)

## Common Integration Points

### Session Management

- Sesión iniciada en `AuthController::__construct()`
- Usuario actual: `$_SESSION['usuario']` (null si no autenticado)
- Logout destruye sesión: `session_destroy()`

### Navigation Structure

- Header define menú con dropdowns para módulos (Personal, Operacional, Financiero)
- Links en header: `dashboard.php`, `clientes.php`, `empleados.php`, etc.
- Todos los controladores cargan automáticamente via `require_once` en vistas
- Navbar optimizado con flexbox para alineación de iconos y texto

### Performance & Optimization

- **Lazy Loading**: Implementado para imágenes (nativo), contenido dinámico (Intersection Observer), y tablas (paginación)
- Ver `LAZY_LOADING_GUIDE.md` para documentación completa
- Funciones disponibles: `loadSectionData()`, `loadTablePage()`, `prefetchResource()`, `preloadResource()`

### Seguridad (AuthController)

- Validación contra SQL injection: sanitización de entrada, prepared statements
- Rate limiting: máx 5 intentos de login = bloqueo 15 minutos
- Hasheo de contraseñas con `password_hash(PASSWORD_DEFAULT)`
- Logging de intentos (exitosos y fallidos)

### Existing Modules

- **Gestión de Personal**: Empleados, Jornales
- **Operacional**: Cosechas, Actividades
- **Financiero**: Ingresos, Egresos, Ventas
- **Administrativo**: Clientes, Proveedores, Reportes

## Security Features

### CSRF Protection (Cross-Site Request Forgery)

- Token `_csrf_token` requerido en todos los formularios POST
- Generar con `SecurityManager::generateCSRFToken()` o helper `csrfToken()`
- Validar en controladores con `SecurityManager::validateCSRFFromRequest()`
- Tokens válidos por 1 hora, luego expiran automáticamente

### XSS Protection (Cross-Site Scripting)

- Sanitizar entrada: `SecurityManager::sanitizeInput($data, 'type')`
- Escapar salida en HTML: `SecurityManager::escapeHTML($var)`
- En atributos: `SecurityManager::escapeAttribute($var)`
- En JSON: `SecurityManager::escapeJSON($data)`
- Tipos soportados: `text`, `email`, `url`, `int`, `float`, `html`

### Cookie Management & Remember Me

- Gestión segura con `SecurityManager::setCookie()`, `getCookie()`, `deleteCookie()`
- Todos los cookies con atributos: HttpOnly, Secure, SameSite=Strict
- Remember Me: 30 días de auto-login con validación de token
- Métodos: `setRememberMeCookie($userId, $token, $days)`, `getRememberMeCookie()`, `validateRememberMeCookie()`
- Cifrado de valores con `encryptCookieValue()` y `decryptCookieValue()`
- Configuración automática: `SecurityManager::configureSessionCookies()` en cada página
- Ver `COOKIES_GUIDE.md` para patrones de implementación

### Security Headers

- Automáticos en cada página via `SecurityManager::setSecurityHeaders()`
- Incluye X-Frame-Options, CSP, X-XSS-Protection, etc.

Ver `SECURITY_GUIDE.md` para documentación completa y ejemplos.

## Tips for Agents

1. **Check Model inheritance** - la mayoría de CRUD logic ya existe en `Model.php`
2. **Respect soft delete** - includes automático de `estado = 'activo'` en Model base, pero custom queries necesitan filtro manual
3. **Session is shared** - todas las vistas acceden a `$_SESSION['usuario']`, no pasar como parámetro
4. **No dependencies** - código usa solo PHP vanilla + PDO, sin Composer/librerías externas
5. **Data flow**: View → Controller → Model → Database → Controller → View (no queries en vistas)
6. **Lazy loading** - usar `data-lazy-load` para contenido dinámico, `loading="lazy"` para imágenes
7. **Security** - validar y sanitizar siempre entrada del usuario, usar prepared statements
