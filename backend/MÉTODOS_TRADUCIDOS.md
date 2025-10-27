# Métodos Traducidos a Español

## Resumen de cambios realizados

Se han traducido los nombres de métodos principales del backend de inglés a español para mejorar la legibilidad y consistencia del código.

## Controladores actualizados:

### ElementoController
- `saveElemento()` → `guardarElemento()`
- `getObjetivoId()` → `obtenerObjetivoId()`
- `getMetasDeObjetivoNew()` → `obtenerMetasDeObjetivoNuevo()`
- `getMetasDeObjetivo()` → `obtenerMetasDeObjetivo()`
- `updateOrder()` → `actualizarOrden()`

### NotaController
- `store()` → `guardar()`
- `update()` → `actualizar()`
- `destroy()` → `eliminar()`
- `notasPorUsuario()` → `obtenerNotasPorUsuario()`

### ObjetivoController
- `store()` → `guardar()`
- `update()` → `actualizar()`
- `destroy()` → `eliminar()`
- `objetivosPorUsuario()` → `obtenerObjetivosPorUsuario()`

### AlarmaController
- `store()` → `guardar()`
- `update()` → `actualizar()`
- `destroy()` → `eliminar()`
- `alarmasPorUsuario()` → `obtenerAlarmasPorUsuario()`

### CalendarioController
- `store()` → `guardar()`
- `update()` → `actualizar()`
- `destroy()` → `eliminar()`
- `calendariosPorUsuario()` → `obtenerCalendariosPorUsuario()`

### EventoController
- `store()` → `guardar()`
- `update()` → `actualizar()`
- `destroy()` → `eliminar()`
- `eventosPorUsuario()` → `obtenerEventosPorUsuario()`

### MetaController
- `store()` → `guardar()`
- `update()` → `actualizar()`
- `destroy()` → `eliminar()`
- `metasPorUsuario()` → `obtenerMetasPorUsuario()`

## Rutas actualizadas:

Todas las rutas en `routes/api.php` han sido actualizadas para referenciar los nuevos nombres de métodos en español.

### Ejemplos de rutas actualizadas:
- `Route::post('/elementos/saveUpdate', [ElementoController::class, 'guardarElemento'])`
- `Route::get('/usuarios/notas', [NotaController::class, 'obtenerNotasPorUsuario'])`
- `Route::get('/usuarios/objetivos', [ObjetivoController::class, 'obtenerObjetivosPorUsuario'])`

## Convenciones de nomenclatura:

- **`store()`** → **`guardar()`**: Crear nuevos registros
- **`update()`** → **`actualizar()`**: Modificar registros existentes
- **`destroy()`** → **`eliminar()`**: Eliminar registros
- **`get**()`** → **`obtener*()`**: Obtener/recuperar datos
- **`*PorUsuario()`** → **`obtener*PorUsuario()`**: Obtener datos filtrados por usuario

## Estado de la traducción:

✅ **Completado**: Controladores principales de elementos
🔄 **Pendiente**: Controladores de sistema (Auth, User, etc.)

Los cambios mantienen la funcionalidad existente mientras mejoran la legibilidad del código para desarrolladores hispanohablantes.