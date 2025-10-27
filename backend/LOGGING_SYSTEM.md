# 📊 Sistema de Logging Completo

## 🎯 **Características Implementadas**

### ✅ **Backend (Laravel)**
- **Logs automáticos de errores** en todos los controladores con try-catch
- **Middleware de logging automático** para capturar errores HTTP (4xx, 5xx)
- **API de logs** para recibir logs desde el frontend
- **Rate limiting** específico para endpoints de logs
- **Sanitización** de datos sensibles (passwords, tokens, etc.)

### ✅ **Frontend (JavaScript)**
- **Logger.js** - Librería completa para el frontend
- **Captura automática** de errores JavaScript no manejados
- **Envío en lotes** para optimizar performance
- **Modo offline** con sincronización automática
- **Múltiples tipos de logs**: error, warning, info, debug, performance, events

---

## 📋 **API Endpoints de Logging**

### **🔴 Logs de Error**
```javascript
POST /api/logs/error
POST /api/logs/authenticated/error

{
  "message": "Error al cargar datos",
  "level": "error",
  "source": "frontend",
  "component": "user-profile",
  "action": "load_data",
  "error_code": "API_ERROR",
  "stack_trace": "Error stack...",
  "additional_data": { "userId": 123 }
}
```

### **📦 Logs en Lote (Offline)**
```javascript
POST /api/logs/batch
POST /api/logs/authenticated/batch

{
  "logs": [
    {
      "message": "Error 1",
      "level": "error",
      "source": "frontend",
      "timestamp": "2024-01-01T12:00:00.000Z"
    },
    {
      "message": "Warning 1",
      "level": "warning",
      "source": "frontend",
      "timestamp": "2024-01-01T12:01:00.000Z"
    }
  ]
}
```

### **📈 Logs de Eventos**
```javascript
POST /api/logs/event
POST /api/logs/authenticated/event

{
  "event_name": "button_clicked",
  "event_category": "user_interaction",
  "event_data": { "button": "login", "page": "home" },
  "session_id": "abc123"
}
```

### **⚡ Logs de Performance**
```javascript
POST /api/logs/performance
POST /api/logs/authenticated/performance

{
  "metric_name": "page_load_time",
  "metric_value": 1234,
  "metric_unit": "ms",
  "component": "dashboard",
  "operation": "initial_load"
}
```

---

## 🚀 **Uso del Frontend Logger**

### **Instalación**
```html
<!-- En tu HTML -->
<script src="/js/logger.js"></script>
```

### **Uso Básico**
```javascript
// Logs manuales
Logger.error('Error al cargar usuario', {
  component: 'user-service',
  userId: 123
});

Logger.warning('Advertencia de timeout');
Logger.info('Usuario logueado exitosamente');
Logger.debug('Datos de debug', { debugInfo: 'value' });

// Eventos de usuario
Logger.event('login_attempt', 'authentication', {
  method: 'google',
  success: true
});

// Métricas de rendimiento
Logger.performance('api_call', 850, 'ms', {
  endpoint: '/api/users',
  method: 'GET'
});
```

### **Configuración Avanzada**
```javascript
const Logger = new FrontendLogger({
  baseUrl: '/api',
  source: 'mobile-app', // o 'web-app'
  batchSize: 20,
  batchTimeout: 10000,
  autoCapture: true
});
```

---

## 🔧 **Configuración del Sistema**

### **Rate Limiting**
```php
// En RouteServiceProvider.php
RateLimiter::for('logs', function (Request $request) {
    return Limit::perMinute(100) // 100 logs por minuto
        ->by($request->user()?->id ?: $request->ip());
});
```

### **Middleware Automático**
```php
// Se aplica automáticamente a todas las rutas API
'api' => [
    // ... otros middlewares
    \App\Http\Middleware\ErrorLoggingMiddleware::class,
],
```

---

## 📱 **Integración con APK (Flutter/Dart)**

### **Ejemplo para Flutter**
```dart
class Logger {
  static const String baseUrl = 'https://tu-api.com/api';

  static Future<void> logError(String message, Map<String, dynamic> data) async {
    try {
      await http.post(
        Uri.parse('$baseUrl/logs/error'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'message': message,
          'level': 'error',
          'source': 'mobile-app',
          'additional_data': data,
        }),
      );
    } catch (e) {
      // Guardar para envío offline
      await _saveOfflineLog(message, data);
    }
  }

  static Future<void> logEvent(String eventName, Map<String, dynamic> data) async {
    // Similar implementación
  }
}
```

---

## 📊 **Beneficios del Sistema**

### **🔍 Para Debugging**
- **Contexto completo**: Usuario, IP, User-Agent, stack trace
- **Logs automáticos**: Todos los errores HTTP se registran automáticamente
- **Correlación**: Session ID para seguir la sesión del usuario

### **📈 Para Analytics**
- **Eventos de usuario**: Clicks, navegación, interacciones
- **Métricas de performance**: Tiempos de carga, respuesta API
- **Patrones de uso**: Identificar funciones más usadas

### **🌐 Para Modo Offline**
- **Queue local**: Los logs se guardan localmente sin conexión
- **Sync automático**: Al recuperar conexión se envían automáticamente
- **Sin pérdida**: Ningún log se pierde por problemas de conectividad

### **🔒 Para Seguridad**
- **Sanitización**: Passwords, tokens y datos sensibles se ocultan
- **Rate limiting**: Previene spam de logs
- **Validación**: Todos los datos se validan antes de guardar

---

## 🛠️ **Estructura de Logs**

### **Log Completo de Error**
```json
{
  "message": "❌ Error guardando elemento",
  "level": "error",
  "timestamp": "2024-01-01T12:00:00.000Z",
  "user_id": 123,
  "ip_address": "192.168.1.1",
  "source": "backend",
  "component": "ElementoController",
  "action": "guardarElemento",
  "error": "Database connection failed",
  "file": "/app/Controllers/ElementoController.php",
  "line": 45,
  "trace": "Stack trace here...",
  "request_data": { "tipo": "nota", "nombre": "Mi nota" },
  "user_agent": "Mozilla/5.0..."
}
```

### **Log de Frontend**
```json
{
  "message": "🌐 [FRONTEND] Error al cargar datos",
  "level": "error",
  "timestamp": "2024-01-01T12:00:00.000Z",
  "user_id": 123,
  "source": "frontend",
  "component": "user-profile",
  "action": "load_user_data",
  "url": "https://app.com/profile",
  "user_agent": "Mozilla/5.0...",
  "stack_trace": "Error stack...",
  "session_id": "abc123"
}
```

---

## 🎯 **Best Practices**

### **✅ Qué Loggear**
- **Errores**: Todos los errores y excepciones
- **Warnings**: Situaciones anómalas pero no críticas
- **Eventos**: Acciones importantes del usuario
- **Performance**: Métricas de tiempo y rendimiento
- **Info**: Eventos importantes del sistema

### **❌ Qué NO Loggear**
- **Passwords**: Nunca loggear contraseñas
- **Tokens**: Datos de autenticación sensibles
- **PII**: Información personal identificable
- **Logs excesivos**: Evitar spam en loops

### **🔧 Configuración Recomendada**
- **Batch size**: 10-20 logs por lote
- **Timeout**: 5-10 segundos para envío
- **Rate limit**: 100 logs por minuto máximo
- **Offline storage**: Máximo 1MB de logs offline

---

## 🚨 **Monitoreo y Alertas**

Los logs se pueden integrar con sistemas de monitoreo como:

- **Laravel Log Viewer** para revisar logs en tiempo real
- **ELK Stack** (Elasticsearch, Logstash, Kibana) para análisis avanzado
- **Sentry** para alertas automáticas de errores críticos
- **New Relic** para métricas de performance

¡El sistema está completamente implementado y listo para usar! 🎉