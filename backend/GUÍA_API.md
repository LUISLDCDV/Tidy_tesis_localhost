# Guía de API - Tidy Backend

## Introducción

Esta guía documenta los endpoints de la API RESTful del backend de Tidy, construida con Laravel y usando autenticación Sanctum.

## Autenticación

### Obtener Token
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "usuario@ejemplo.com",
  "password": "contraseña"
}
```

**Respuesta:**
```json
{
  "token": "your-api-token",
  "user": {
    "id": 1,
    "name": "Usuario",
    "email": "usuario@ejemplo.com"
  }
}
```

### Usar Token
Incluir en todas las peticiones autenticadas:
```http
Authorization: Bearer your-api-token
```

## Endpoints de Elementos

### Obtener Elementos del Usuario
```http
GET /api/usuarios/elementos
Authorization: Bearer {token}
```

**Parámetros opcionales:**
- `tipo`: Filtrar por tipo de elemento (`alarma`, `objetivo`, `meta`, `calendario`, `evento`, `nota`)

**Respuesta:**
```json
[
  {
    "id": 1,
    "tipo": "objetivo",
    "nombre": "Aprender Laravel",
    "descripcion": "Objetivo de aprendizaje",
    "estado": "activo",
    "orden": 1,
    "created_at": "2025-09-21T10:00:00.000000Z",
    "fechaCreacion": "2025-09-21",
    "fechaVencimiento": "2025-12-31",
    "status": "pendiente"
  }
]
```

### Guardar/Actualizar Elemento
```http
POST /api/elementos/saveUpdate
Authorization: Bearer {token}
Content-Type: application/json
```

**Campos comunes:**
```json
{
  "nombre": "Nombre del elemento",
  "tipo": "objetivo",
  "elemento_id": null
}
```

**Por tipo de elemento:**

#### Alarma
```json
{
  "nombre": "Recordatorio importante",
  "tipo": "alarma",
  "fecha": "2025-09-21",
  "hora": "09:00:00",
  "fechaVencimiento": "2025-09-21",
  "horaVencimiento": "09:00:00",
  "intensidad_volumen": 80,
  "configuraciones": {
    "repetir": true,
    "dias": ["lunes", "martes"]
  }
}
```

#### Objetivo
```json
{
  "nombre": "Aprender programación",
  "tipo": "objetivo",
  "fechaCreacion": "2025-09-21",
  "fechaVencimiento": "2025-12-31",
  "tipo_objetivo": "1",
  "status": "pendiente",
  "informacion": "Descripción del objetivo"
}
```

#### Meta
```json
{
  "nombre": "Estudiar Laravel",
  "tipo": "meta",
  "objetivo_id": 1,
  "fechaCreacion": "2025-09-21",
  "fechaVencimiento": "2025-10-31",
  "status": "pendiente",
  "informacion": "Paso específico del objetivo"
}
```

#### Nota
```json
{
  "nombre": "Nota importante",
  "tipo": "nota",
  "fecha": "2025-09-21",
  "tipo_nota_id": 1,
  "contenido": "Contenido de la nota",
  "informacion": "Información adicional",
  "clave": "palabra_clave"
}
```

#### Calendario
```json
{
  "nombre": "Mi Calendario",
  "tipo": "calendario",
  "color": "#3498db",
  "informacion": "Calendario personal"
}
```

#### Evento
```json
{
  "nombre": "Reunión importante",
  "tipo": "evento",
  "calendario_id": 1,
  "fechaVencimiento": "2025-09-21",
  "horaVencimiento": "14:00:00",
  "informacion": "Descripción del evento",
  "gps": {
    "latitud": -34.6037,
    "longitud": -58.3816,
    "direccion": "Buenos Aires, Argentina"
  },
  "clima": {
    "habilitado": true,
    "ciudad": "Buenos Aires"
  }
}
```

### Eliminar Elemento
```http
POST /api/elementos/eliminarElemento/{id}
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "message": "Elemento eliminado exitosamente"
}
```

### Actualizar Orden
```http
POST /api/elementos/updateOrder
Authorization: Bearer {token}
Content-Type: application/json

{
  "elementos": [
    {"id": 1, "orden": 1},
    {"id": 2, "orden": 2}
  ]
}
```

## Endpoints Específicos

### Objetivos

#### Obtener Objetivos del Usuario
```http
GET /api/usuarios/objetivos
Authorization: Bearer {token}
```

#### Obtener ID de Objetivo por Elemento
```http
GET /api/elementos/objetivo-id/{elementoId}
Authorization: Bearer {token}
```

#### Obtener Metas de un Objetivo
```http
GET /api/elementos/{elementoId}/metas
Authorization: Bearer {token}
```

### Notas

#### Obtener Notas del Usuario
```http
GET /api/usuarios/notas
Authorization: Bearer {token}
```

### Alarmas

#### Obtener Alarmas del Usuario
```http
GET /api/usuarios/alarmas
Authorization: Bearer {token}
```

### Calendarios

#### Obtener Calendarios del Usuario
```http
GET /api/usuarios/calendarios
Authorization: Bearer {token}
```

#### Obtener Eventos de un Calendario
```http
GET /api/usuarios/{idCalendario}/eventos
Authorization: Bearer {token}
```

### Metas

#### Obtener Metas de un Objetivo
```http
GET /api/usuarios/{idObjetivo}/metas
Authorization: Bearer {token}
```

## Códigos de Estado HTTP

- `200 OK` - Operación exitosa
- `201 Created` - Recurso creado exitosamente
- `400 Bad Request` - Datos inválidos
- `401 Unauthorized` - Token inválido o faltante
- `404 Not Found` - Recurso no encontrado
- `422 Unprocessable Entity` - Errores de validación
- `500 Internal Server Error` - Error del servidor

## Ejemplos de Respuestas de Error

### Error de Validación (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "nombre": ["El campo nombre es obligatorio."],
    "tipo": ["El tipo seleccionado es inválido."]
  }
}
```

### Error de Autenticación (401)
```json
{
  "message": "Unauthenticated."
}
```

### Error de Autorización (403)
```json
{
  "error": "Usuario no autenticado"
}
```

## Logging

El sistema incluye logging detallado para debugging:

```php
// Los logs incluyen emojis para fácil identificación
\Log::info("💾 ElementoController - Datos recibidos:", $data);
\Log::info("✅ Elemento creado exitosamente", ['id' => $elemento->id]);
\Log::error("❌ Error al procesar:", ['error' => $e->getMessage()]);
```

## Rate Limiting

La API incluye limitación de requests:
- **Límite**: 60 requests por minuto por usuario autenticado
- **Header de respuesta**: `X-RateLimit-Remaining`

## Versionado

- **Versión actual**: v1
- **URL base**: `/api/v1` (futuras versiones)
- **Compatibilidad**: Mantenida por al menos 1 año

## Testing

### Ejemplo con cURL
```bash
# Obtener elementos
curl -X GET \
  http://localhost:8000/api/usuarios/elementos \
  -H 'Authorization: Bearer your-token' \
  -H 'Accept: application/json'

# Crear objetivo
curl -X POST \
  http://localhost:8000/api/elementos/saveUpdate \
  -H 'Authorization: Bearer your-token' \
  -H 'Content-Type: application/json' \
  -d '{
    "nombre": "Nuevo objetivo",
    "tipo": "objetivo",
    "fechaCreacion": "2025-09-21",
    "tipo_objetivo": "1",
    "status": "pendiente"
  }'
```

---

**Nota**: Esta API está en activo desarrollo. Consultar logs del servidor para debugging y verificar los endpoints más recientes.