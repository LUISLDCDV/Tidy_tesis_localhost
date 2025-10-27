# REFERENCIA RÁPIDA - Archivos a Eliminar/Revisar

## 1. TABLA DE CONTROLADORES

| Archivo | Ruta | Uso | Acción | Riesgo |
|---------|------|-----|--------|--------|
| ExampleController.php | app/Http/Controllers/ | NINGUNO | ELIMINAR | MUY BAJO |
| AuthController copy.php | app/Http/Controllers/ | NINGUNO | ELIMINAR | MUY BAJO |
| NotificacionController.php | app/Http/Controllers/ | NINGUNO | ELIMINAR | MUY BAJO |
| ProfileController.php | app/Http/Controllers/ | NO REGISTRADO | EVALUAR | BAJO |
| PuntajeController.php | app/Http/Controllers/ | NO REGISTRADO | EVALUAR | BAJO |
| UsuarioAdminController.php | app/Http/Controllers/ | NO REGISTRADO | EVALUAR | BAJO |
| MedioDePagoController.php | app/Http/Controllers/ | NO REGISTRADO | EVALUAR | BAJO |
| UsuarioCuentaController.php | app/Http/Controllers/ | NO REGISTRADO | EVALUAR | BAJO |

---

## 2. TABLA DE MODELOS

| Modelo | Ruta | Controlador Asociado | Acción | Riesgo |
|--------|------|----------------------|--------|--------|
| Puntaje.php | app/Models/ | PuntajeController (no usado) | EVALUAR | BAJO |
| Premio.php | app/Models/ | PuntajeController (no usado) | EVALUAR | BAJO |
| Permiso.php | app/Models/ | NINGUNO | EVALUAR | BAJO |
| UsuarioAdmin.php | app/Models/ | UsuarioAdminController (no usado) | EVALUAR | BAJO |
| MedioDePago.php | app/Models/ | MedioDePagoController (no usado) | EVALUAR | BAJO |
| Notificacion.php | app/Models/ | NINGUNO (**) | ELIMINAR | MEDIO |
| GPSAlarm.php | app/Models/ | RUTAS COMENTADAS | EVALUAR | BAJO |
| GPSAlarmTrigger.php | app/Models/ | RUTAS COMENTADAS | EVALUAR | BAJO |

(**) Conflicto directo con Notification.php (modelo moderno)

---

## 3. TABLA DE MIGRACIONES

| Migración | Fecha | Acción | Duplicada Con | Riesgo |
|-----------|-------|--------|----------------|--------|
| 2025_10_12_025956_make_phone_nullable_in_users_table.php | 12/oct | ELIMINAR | 2025_10_25_180511 | MUY BAJO |
| 2025_10_25_180511_update_users_phone_column_nullable.php | 25/oct | MANTENER | (versión mejorada) | - |

---

## 4. TABLA DE MIDDLEWARE

| Middleware | Ruta | Registrado | Acción | Riesgo |
|------------|------|-----------|--------|--------|
| ThrottleApiRequests.php | app/Http/Middleware/ | NO | EVALUAR | BAJO |
| Cors.php | app/Http/Middleware/ | SÍ | MANTENER | - |
| ErrorLoggingMiddleware.php | app/Http/Middleware/ | SÍ | MANTENER | - |
| EnsureNotaTypesExist.php | app/Http/Middleware/ | SÍ | MANTENER | - |

---

## 5. TABLA DE TESTS

| Test | Ruta | Contenido | Acción | Riesgo |
|------|------|-----------|--------|--------|
| ExampleTest.php | tests/Feature/ | Ejemplo vacío | ELIMINAR | MUY BAJO |
| ExampleTest.php | tests/Unit/ | Ejemplo vacío | ELIMINAR | MUY BAJO |

---

## 6. TABLA DE RUTAS COMENTADAS

| Sección | Líneas | Rutas | Estado | Acción |
|---------|--------|-------|--------|--------|
| Sincronización | 60-68 | 6 rutas | COMENTADA | EVALUAR |
| Notificaciones Móviles | 70-78 | 6 rutas | COMENTADA | EVALUAR |
| Alarmas GPS | 249-258 | 7 rutas | COMENTADA | EVALUAR |
| Alarmas Móviles | 261-272 | 8 rutas | COMENTADA | EVALUAR |
| Migraciones (CRÍTICA) | 269-286 | 1 ruta | COMENTADA | ELIMINAR |

---

## 7. VERIFICACIÓN PRE-ELIMINACIÓN

### Para cada controlador:
- [ ] Buscar en routes/api.php
- [ ] Buscar en routes/web.php
- [ ] Grep global: `grep -r "NombreController" /app`
- [ ] Verificar no está en bootstrap/app.php

### Para cada modelo:
- [ ] Buscar en controllers
- [ ] Buscar en factories/seeders
- [ ] Buscar en migrations
- [ ] Grep global: `grep -r "NombreModel" /app`

### Para cada middleware:
- [ ] Verificar no está en Kernel.php
- [ ] Verificar no está en rutas
- [ ] Grep global: `grep -r "NombreMiddleware" /app`

---

## 8. CHECKLIST EJECUCIÓN

### Fase 1: INMEDIATA (Riesgo MUY BAJO)

```
[ ] Crear rama: git checkout -b cleanup/remove-unused-files
[ ] Eliminar ExampleController.php
[ ] Eliminar AuthController copy.php
[ ] Eliminar NotificacionController.php
[ ] Eliminar tests/Feature/ExampleTest.php
[ ] Eliminar tests/Unit/ExampleTest.php
[ ] Eliminar migración 2025_10_12_025956...
[ ] Editar routes/web.php - eliminar líneas 269-286
[ ] Commit: "chore: remove unused files and example code"
[ ] Push y crear PR
[ ] Testing: php artisan routes:list
[ ] Merge si todo está OK
```

### Fase 2: CORTO PLAZO (Después de 1 semana)

```
[ ] Validar dependencias de ProfileController
[ ] Validar dependencias de PuntajeController + Puntaje + Premio
[ ] Validar dependencias de UsuarioAdminController + UsuarioAdmin
[ ] Validar dependencias de MedioDePagoController + MedioDePago
[ ] Validar dependencias de Permiso
[ ] Validar dependencias de Notificacion (conflicto)
[ ] Validar dependencias de GPSAlarm/GPSAlarmTrigger
[ ] Validar ThrottleApiRequests
[ ] Crear PR por categoría
[ ] Testing exhaustivo en dev/staging
```

---

## 9. HERRAMIENTAS DE BÚSQUEDA

### Buscar referencias a un archivo:
```bash
grep -r "ExampleController" /home/luis/Escritorio/tesis/v2/Tidy/backend/app
grep -r "Puntaje" /home/luis/Escritorio/tesis/v2/Tidy/backend/app --include="*.php"
```

### Buscar importaciones:
```bash
grep -r "use App.*ExampleController" /app
grep -r "use App.Models.Puntaje" /app
```

### Contar referencias:
```bash
grep -r "ExampleController" /app | wc -l
```

---

## 10. RESUMEN POR FASES

### Fase 1 - Riesgo MUY BAJO (2 horas)
- 6 archivos a eliminar
- 1 modificación menor (rutas)
- ~50 líneas de código
- 100% seguro eliminar

### Fase 2 - Riesgo BAJO (1-2 días)
- 12-14 archivos a revisar
- Requiere búsqueda de dependencias
- ~150-200 líneas de código
- 95% seguro eliminar (con validación)

### Fase 3 - Riesgo BAJO-MEDIO (1-2 semanas)
- Consolidación de features
- Limpieza de rutas comentadas
- Documentación
- 80% seguro (requiere refactoring)

---

## 11. IMPACTO CUANTIFICABLE

| Métrica | Antes | Después | Mejora |
|---------|-------|---------|--------|
| Controllers | 49 | 41-43 | 12-16% |
| Models | 21 | 13-15 | 28-38% |
| Migrations | 43 | 41 | 4.6% |
| Middleware | 13 | 11-12 | 8-15% |
| Dead Code Lines | 500-700 | 0-100 | 85-100% |
| Project Clarity | Bajo | Medio-Alto | 20-30% |

---

## 12. CONTACTO / REFERENCIAS

- Reportes detallados: Ver ANALYSIS_REPORT.md
- Guía de acción: Ver ACTION_GUIDE.md
- Resumen ejecutivo: Ver SUMMARY.txt

