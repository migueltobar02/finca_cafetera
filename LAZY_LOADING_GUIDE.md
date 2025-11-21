# Guía de Lazy Loading - Finca Cafetera

## Descripción General

Se ha implementado lazy loading en el sistema para optimizar el rendimiento y reducir el consumo inicial de datos. El lazy loading carga recursos (imágenes, contenido dinámico, tablas) solo cuando se necesitan.

## Características Implementadas

### 1. Lazy Loading Nativo para Imágenes

**Uso automático:**

```html
<img src="imagen.jpg" alt="Descripción" loading="lazy" decoding="async" />
```

El sistema automáticamente agrega `loading="lazy"` a todas las imágenes. Esto es soportado nativamente por navegadores modernos.

**Ejemplo en vistas:**

```php
<img src="ruta/imagen.jpg" alt="Imagen de perfil">
```

### 2. Lazy Loading Avanzado con Intersection Observer

Para imágenes que necesitan manejo especial, usa el atributo `data-src`:

```html
<img
  class="lazy"
  data-src="imagen-real.jpg"
  src="placeholder.jpg"
  alt="Descripción"
/>
```

El sistema automáticamente detectará y cargará estas imágenes cuando se acerquen a la vista.

### 3. Lazy Loading para Contenido Dinámico

Carga secciones bajo demanda cuando se hacen visibles:

```html
<div data-lazy-load="api/endpoint" class="card">
  <!-- El contenido se cargará automáticamente cuando sea visible -->
</div>
```

**Uso en controladores:**

```php
// En un endpoint que retorna JSON
$datos = $controller->getData();
header('Content-Type: application/json');
echo json_encode(['html' => '<p>Contenido cargado dinámicamente</p>']);
```

### 4. Lazy Loading para Tablas Grandes

Carga filas bajo demanda para tablas extensas:

```html
<table data-lazy-load-table="api/tabla" data-page="1">
  <tbody>
    <!-- Las filas se cargarán bajo demanda -->
  </tbody>
</table>
```

## Funciones JavaScript Disponibles

### `loadImage(img)`

Fuerza la carga de una imagen lazy específica:

```javascript
const img = document.querySelector("img.lazy");
loadImage(img);
```

### `loadSectionData(url, section)`

Carga contenido dinámico en una sección:

```javascript
const section = document.getElementById("mi-seccion");
loadSectionData("/api/datos", section);
```

### `loadTablePage(url, table, page)`

Carga una página de tabla:

```javascript
const table = document.querySelector("table");
loadTablePage("/api/tabla", table, 1);
```

### `prefetchResource(url)`

Prefetch de recurso para carga más rápida:

```javascript
prefetchResource("/public/datos-importantes.json");
```

### `preloadResource(url, as)`

Preload de recurso crítico:

```javascript
preloadResource("/public/js/critico.js", "script");
```

## Mejores Prácticas

### Para Imágenes

1. **Usa imágenes responsivas:**

   ```html
   <img
     loading="lazy"
     src="imagen.jpg"
     srcset="
       imagen-small.jpg   480w,
       imagen-medium.jpg  800w,
       imagen-large.jpg  1200w
     "
     sizes="(max-width: 480px) 100vw, (max-width: 800px) 80vw, 60vw"
     alt="Descripción"
   />
   ```

2. **Evita lazy loading para imágenes above-the-fold:**

   ```html
   <!-- Imágenes al cargar la página NO deben usar lazy loading -->
   <img src="hero.jpg" alt="Hero Image" />

   <!-- Imágenes debajo sí pueden usar lazy loading -->
   <img src="content.jpg" loading="lazy" alt="Content" />
   ```

### Para Contenido Dinámico

1. **Estructura clara:**

   ```html
   <div data-lazy-load="/api/estadisticas" class="card">
     <div class="placeholder">
       <p>Cargando estadísticas...</p>
     </div>
   </div>
   ```

2. **Manejo de errores en API:**
   ```php
   // Siempre retorna JSON
   try {
       $datos = $controller->getDatos();
       echo json_encode(['html' => renderizar($datos)]);
   } catch (Exception $e) {
       echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
   }
   ```

### Para Tablas

1. **Paginación backend:**

   ```php
   // En tu controlador, soporta parámetro ?page=
   public function getTablePage($page = 1) {
       $perPage = 20;
       $offset = ($page - 1) * $perPage;
       $rows = $this->model->getWithLimit($perPage, $offset);

       return json_encode(['rows' => $this->formatRows($rows)]);
   }
   ```

2. **Formato de respuesta:**
   ```json
   {
     "rows": [
       "<td>Data 1</td><td>Data 2</td>",
       "<td>Data 3</td><td>Data 4</td>"
     ]
   }
   ```

## Monitoreo de Rendimiento

Para verificar que lazy loading funciona correctamente:

1. **Abre DevTools (F12)**
2. **Network Tab**: Verifica que las imágenes se cargan solo cuando se hacen visibles
3. **Performance Tab**: Mide el tiempo inicial de carga
4. **Console**: Busca errores de carga

## Compatibilidad

- **Lazy loading nativo (`loading="lazy"`)**: Chrome 76+, Firefox 75+, Safari 15.1+, Edge 79+
- **Intersection Observer**: Compatible con todos los navegadores modernos (fallback graceful)

## Casos de Uso Principales

### Dashboard

- Imágenes de estadísticas con lazy loading
- Gráficos que se cargan bajo demanda
- Tablas de actividad reciente con paginación

### Listados (Clientes, Empleados)

- Tablas grandes con lazy loading de filas
- Búsqueda con carga dinámica de resultados

### Formularios

- Selects dinámicos que cargan opciones bajo demanda
- Validaciones asincrónicas

## Desactivar Lazy Loading

Para elementos que no deben usar lazy loading:

```html
<!-- Imagen que se carga inmediatamente -->
<img src="critica.jpg" alt="Crítica" class="no-lazy" />

<!-- O sin el atributo data-lazy-load -->
<div class="card">Contenido estático</div>
```

## Conclusión

El lazy loading mejora significativamente:

- ⚡ Velocidad inicial de carga
- 📉 Consumo de ancho de banda
- 💾 Uso de memoria del navegador
- 🎯 Experiencia del usuario

Úsalo estratégicamente en áreas con mucho contenido o imágenes.
