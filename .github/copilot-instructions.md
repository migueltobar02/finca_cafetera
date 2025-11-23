# Instrucciones para agentes (Copilot)

Resumen rápido

- Proyecto: `finca_cafetera` — PHP vanilla con arquitectura MVC ligera.
- Entradas públicas: `public/` (vistas / puntos de entrada). Autoload en `app/autoload.php`.

Arquitectura & motivos clave

- Código PHP sin frameworks: controladores en `app/controllers/`, modelos en `app/models/`, vistas en `public/`.
- `Database.php` implementa un singleton PDO: todas las conexiones usan la misma instancia.
- `Model.php` ofrece CRUD genérico y soporte de "soft delete" usando la columna `estado` (`activo`/`inactivo`).

Patrones y convenciones del proyecto

- Modelos: heredan de `Model` y llaman `parent::__construct('nombre_tabla')`.
  Ejemplo: `app/models/Cliente.php`.
- Soft delete: `delete()` actualiza `estado = 'inactivo'`. Las consultas genéricas excluyen registros inactivos.
- Controladores: no deben imprimir salida; retornan datos para que las vistas los rendericen.
  Ejemplo: `app/controllers/ClientesController.php`.
- Vistas: archivos PHP simples en `public/`. Incluyen `app/views/components/header.php`.
- Seguridad: usar `SecurityManager` para CSRF (`_csrf_token`), sanitización y escape de salida.

Flujos comunes y comandos útiles

- Levantar servidor PHP local para desarrollo:
  `php -S localhost:8000 -t public`
- Importar DB de desarrollo:
  `mysql -u root finca_cafetera < database/finca_cafetera2.sql`
- Las variables de entorno se cargan vía `app/helpers/EnvLoader.php` y son usadas por `app/config/database.php`.

Puntos de integración importantes

- Sesión: `$_SESSION['usuario']` contiene `[id, username, nombre_completo, rol]`. Autorización verificable con `AuthController::checkAuth()`.
- CSRF: todos los formularios POST usan `_csrf_token` validado por `SecurityManager`.
- Cookies/Remember-me: gestionadas por `SecurityManager` con atributos `HttpOnly`, `Secure`, `SameSite=Strict`.

Qué editar y cómo añadir características

- Añadir un nuevo CRUD:
  1. Crear `app/models/TuModelo.php` extendiendo `Model`.
  2. Crear `app/controllers/TuModeloController.php` con métodos `index()`, `crear()`, `actualizar()`, `eliminar()`.
  3. Nueva vista en `public/tu_modelo.php` que `require_once '../app/controllers/TuModeloController.php';` y llama al controller.
- Para consultas personalizadas usar prepared statements (`$this->db->prepare()` o `$this->query()` del `Model`).

Buenas prácticas específicas (descubiertas en el repo)

- Siempre filtrar `estado = 'activo'` en queries ad-hoc.
- Validación de entrada en controladores; modelos solo persisten.
- Reutilizar `app/views/components/header.php` en vistas públicas para mantener menú y sesión.

Limitaciones y cosas a tener en cuenta

- No hay tests automatizados ni pipeline de CI definido.
- No hay composer / dependencias externas — todo es PHP + PDO.
- Las credenciales por defecto están en `app/config/database.php` para desarrollo local; producción usa `.env` (no versionado).

Dónde mirar primero

- `app/autoload.php`, `app/config/database.php`, `app/models/Model.php`, `app/models/Database.php`,
  `app/controllers/AuthController.php`, `app/models/SecurityManager.php`, y `public/index.php`.

Si algo no está claro

- Dime qué sección quieres que amplíe: autenticación, patrón Model/Controller, CSRF, o ejemplo concreto de CRUD.

---

Por favor revisa y dime si quieres que incluya ejemplos de código concretos o pasos de debugging adicionales.

# Copilot Instructions - Finca Cafetera

## Architecture Overview

**Finca Cafetera** es un sistema de gestión para una finca cafetera construido con **PHP vanilla** (sin frameworks) usando una arquitectura **MVC ligera** con separación clara entre controladores, modelos y vistas.

### Directory Structure

- **`app/autoload.php`** - Autocargador PSR-4 que resuelve clases desde `models/` y `controllers/`
- **`app/config/database.php`** - Configuración de conexión MySQL con variables de entorno
- **`app/helpers/EnvLoader.php`** - Cargador de variables de entorno desde `.env`
- **`app/models/`** - Lógica de negocio con patrón **Singleton para Database**
- **`app/controllers/`** - Manejo de peticiones y orquestación de modelos
- **`app/views/`** - Componentes reutilizables (solo `header.php` y formularios)
- **`public/`** - Puntos de entrada (entry points) que requieren autenticación
- **`database/`** - Schema SQL inicial
- **`.env`** - Variables de entorno (NO versionado, usar `.env.example` como referencia)

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
   - Credenciales cargadas desde variables de entorno via `DatabaseConfig::getConfig()`

3. **Environment Variables** (`.env`):

   - Variables: `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`
   - Cargadas automáticamente por `EnvLoader::load()` en `database.php`
   - Valores por defecto: localhost:3306, root sin contraseña, base `finca_cafetera`
   - Ver `ENV_GUIDE.md` para documentación completa

4. **Authentication Model**:

   - Session-based con `$_SESSION['usuario']` almacenando `[id, username, nombre_completo, rol]`
   - Verificación de autenticación en `AuthController::checkAuth()` redirige a login
   - Contraseñas hasheadas con `password_hash(PASSWORD_DEFAULT)`

5. **View Layer**:
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

## Environment Variables

### Configuration with EnvLoader

- Variables almacenadas en archivo `.env` (NO versionado en git)
- `EnvLoader::load()` carga automáticamente en constructor de `DatabaseConfig`
- Acceso: `EnvLoader::get('MYSQLHOST', 'default_value')`
- Verificar existencia: `EnvLoader::has('MYSQLHOST')`
- Valores esperados: `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`
- Ver `ENV_GUIDE.md` para guía de configuración completa

## Tips for Agents

1. **Check Model inheritance** - la mayoría de CRUD logic ya existe en `Model.php`
2. **Respect soft delete** - includes automático de `estado = 'activo'` en Model base, pero custom queries necesitan filtro manual
3. **Session is shared** - todas las vistas acceden a `$_SESSION['usuario']`, no pasar como parámetro
4. **No dependencies** - código usa solo PHP vanilla + PDO, sin Composer/librerías externas
5. **Data flow**: View → Controller → Model → Database → Controller → View (no queries en vistas)
6. **Lazy loading** - usar `data-lazy-load` para contenido dinámico, `loading="lazy"` para imágenes
7. **Security** - validar y sanitizar siempre entrada del usuario, usar prepared statements
