# Servicios - Documentación

## Descripción General

Los servicios en Tidy proporcionan una capa de abstracción para operaciones específicas como geolocalización, APIs externas, y lógica de negocio compleja.

## Arquitectura de Servicios

```
services/
├── api.js                    # Cliente HTTP base
├── geoAlarmService.js        # Servicio de geofencing y alarmas GPS
├── weatherService.js         # Servicio de clima
├── authService.js           # Servicios de autenticación
├── notificationService.js   # Gestión de notificaciones
├── storageService.js        # Gestión de almacenamiento local
├── syncService.js           # Sincronización offline
└── exportService.js         # Exportación de datos
```

## API Service (api.js)

### Descripción

Cliente HTTP centralizado basado en Axios con interceptores para autenticación, manejo de errores y logging.

### Configuración

```javascript
import api from '@/services/api';

// Configuración por defecto
const apiConfig = {
  baseURL: 'http://localhost:880/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
};
```

### Uso Básico

```javascript
// GET request
const response = await api.get('/alarms');

// POST request
const newAlarm = await api.post('/alarms', alarmData);

// PUT request
const updatedAlarm = await api.put(`/alarms/${id}`, updates);

// DELETE request
await api.delete(`/alarms/${id}`);
```

### Interceptores

#### Request Interceptor

```javascript
api.interceptors.request.use(
  (config) => {
    // Agregar token de autenticación
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Logging de requests
    console.log('API Request:', config);

    return config;
  },
  (error) => {
    console.error('Request Error:', error);
    return Promise.reject(error);
  }
);
```

#### Response Interceptor

```javascript
api.interceptors.response.use(
  (response) => {
    console.log('API Response:', response);
    return response.data;
  },
  (error) => {
    // Manejo de errores globales
    if (error.response?.status === 401) {
      // Token expirado - redirigir a login
      router.push('/login');
    }

    return Promise.reject(error);
  }
);
```

## GeoAlarm Service (geoAlarmService.js)

### Descripción

Servicio avanzado para geofencing y alarmas basadas en ubicación GPS.

### Características

- Monitoreo continuo de ubicación
- Cálculo de distancias usando fórmula Haversine
- Gestión de múltiples zonas geográficas
- Notificaciones automáticas
- Persistencia local para funcionamiento offline

### API Principal

#### `startGeoAlarmMonitoring()`

Inicia el monitoreo de geolocalización.

```javascript
import geoAlarmService from '@/services/geoAlarmService';

// Iniciar monitoreo
await geoAlarmService.startGeoAlarmMonitoring();
```

#### `addGeoAlarm(alarm)`

Agrega una nueva geo-alarma.

```javascript
const geoAlarm = {
  id: 'alarm_123',
  name: 'Reunión en oficina',
  latitude: 40.4168,
  longitude: -3.7038,
  radius: 100, // metros
  triggerOnEnter: true,
  triggerOnExit: false
};

await geoAlarmService.addGeoAlarm(geoAlarm);
```

#### `removeGeoAlarm(alarmId)`

Elimina una geo-alarma.

```javascript
await geoAlarmService.removeGeoAlarm('alarm_123');
```

#### `getCurrentLocation()`

Obtiene la ubicación actual del usuario.

```javascript
const location = await geoAlarmService.getCurrentLocation();
console.log(`Lat: ${location.latitude}, Lng: ${location.longitude}`);
```

### Configuración Avanzada

```javascript
const config = {
  enableHighAccuracy: true,
  timeout: 10000,
  maximumAge: 300000, // 5 minutos
  distanceFilter: 10, // metros
  updateInterval: 30000 // 30 segundos
};

await geoAlarmService.configure(config);
```

### Eventos

```javascript
// Escuchar eventos de geo-alarmas
geoAlarmService.on('locationUpdate', (location) => {
  console.log('Nueva ubicación:', location);
});

geoAlarmService.on('geoAlarmTriggered', (alarm) => {
  console.log('Alarma activada:', alarm.name);
});

geoAlarmService.on('enterZone', (alarm) => {
  console.log('Entrando en zona:', alarm.name);
});

geoAlarmService.on('exitZone', (alarm) => {
  console.log('Saliendo de zona:', alarm.name);
});
```

## Weather Service (weatherService.js)

### Descripción

Servicio para obtener información meteorológica y mostrar clima en eventos.

### API Principal

#### `getCurrentWeather(location)`

Obtiene el clima actual para una ubicación.

```javascript
import weatherService from '@/services/weatherService';

const weather = await weatherService.getCurrentWeather({
  latitude: 40.4168,
  longitude: -3.7038
});

console.log(`Temperatura: ${weather.temperature}°C`);
console.log(`Condición: ${weather.condition}`);
```

#### `getForecast(location, days)`

Obtiene pronóstico del tiempo.

```javascript
const forecast = await weatherService.getForecast(location, 7);

forecast.forEach(day => {
  console.log(`${day.date}: ${day.temperature}°C, ${day.condition}`);
});
```

#### `getWeatherByCity(cityName)`

Obtiene clima por nombre de ciudad.

```javascript
const weather = await weatherService.getWeatherByCity('Madrid');
```

### Configuración

```javascript
const weatherConfig = {
  apiKey: 'your_api_key',
  provider: 'openweathermap', // o 'weatherapi'
  units: 'metric',
  language: 'es'
};

weatherService.configure(weatherConfig);
```

## Auth Service (authService.js)

### Descripción

Servicios de autenticación y gestión de tokens.

### API Principal

#### `login(credentials)`

Autentica al usuario.

```javascript
import authService from '@/services/authService';

const response = await authService.login({
  email: 'usuario@ejemplo.com',
  password: 'contraseña123'
});

console.log('Token:', response.token);
console.log('Usuario:', response.user);
```

#### `logout()`

Cierra sesión del usuario.

```javascript
await authService.logout();
```

#### `refreshToken()`

Renueva el token de acceso.

```javascript
const newToken = await authService.refreshToken();
```

#### `resetPassword(email)`

Solicita reset de contraseña.

```javascript
await authService.resetPassword('usuario@ejemplo.com');
```

### Gestión de Tokens

```javascript
// Verificar si el token es válido
const isValid = authService.isTokenValid();

// Obtener tiempo de expiración
const expiresIn = authService.getTokenExpiration();

// Auto-renovación de token
authService.enableAutoRefresh();
```

## Notification Service (notificationService.js)

### Descripción

Gestión unificada de notificaciones push, in-app y por email.

### API Principal

#### `requestPermission()`

Solicita permisos para notificaciones.

```javascript
import notificationService from '@/services/notificationService';

const granted = await notificationService.requestPermission();
if (granted) {
  console.log('Permisos concedidos');
}
```

#### `showNotification(options)`

Muestra una notificación.

```javascript
await notificationService.showNotification({
  title: 'Nueva alarma',
  body: 'Es hora de tu reunión',
  icon: '/icons/alarm.png',
  badge: '/icons/badge.png',
  tag: 'alarm_123',
  actions: [
    { action: 'dismiss', title: 'Descartar' },
    { action: 'snooze', title: 'Posponer' }
  ]
});
```

#### `scheduleNotification(alarm)`

Programa una notificación para el futuro.

```javascript
await notificationService.scheduleNotification({
  id: 'alarm_123',
  title: 'Reunión importante',
  scheduledTime: new Date('2024-01-15T10:00:00'),
  recurring: 'daily'
});
```

#### `cancelNotification(id)`

Cancela una notificación programada.

```javascript
await notificationService.cancelNotification('alarm_123');
```

### Configuración

```javascript
const notificationConfig = {
  vapidKey: 'your_vapid_key',
  serviceWorkerPath: '/sw.js',
  defaultIcon: '/icons/default.png',
  defaultBadge: '/icons/badge.png'
};

notificationService.configure(notificationConfig);
```

## Storage Service (storageService.js)

### Descripción

Servicio unificado para gestión de almacenamiento local y remoto.

### API Principal

#### `set(key, value, options)`

Almacena un valor.

```javascript
import storageService from '@/services/storageService';

// Almacenamiento simple
await storageService.set('user_preferences', preferences);

// Con opciones
await storageService.set('temporary_data', data, {
  expiry: 3600000, // 1 hora
  encrypt: true,
  sync: true
});
```

#### `get(key, defaultValue)`

Obtiene un valor almacenado.

```javascript
const preferences = await storageService.get('user_preferences', {});
```

#### `remove(key)`

Elimina un valor.

```javascript
await storageService.remove('temporary_data');
```

#### `clear(pattern)`

Limpia el almacenamiento.

```javascript
// Limpiar todo
await storageService.clear();

// Limpiar por patrón
await storageService.clear('cache_*');
```

### Características Avanzadas

```javascript
// Encriptación automática
await storageService.set('sensitive_data', data, { encrypt: true });

// Compresión para datos grandes
await storageService.set('large_dataset', data, { compress: true });

// Sincronización automática con backend
await storageService.set('user_data', data, { sync: true });

// Expiración automática
await storageService.set('session_data', data, {
  expiry: Date.now() + 3600000
});
```

## Sync Service (syncService.js)

### Descripción

Servicio para sincronización de datos en modo offline y manejo de conflictos.

### API Principal

#### `startSync()`

Inicia la sincronización automática.

```javascript
import syncService from '@/services/syncService';

await syncService.startSync();
```

#### `queueOperation(operation)`

Encola una operación para sincronización.

```javascript
await syncService.queueOperation({
  type: 'CREATE',
  collection: 'alarms',
  data: newAlarmData,
  timestamp: Date.now()
});
```

#### `syncNow()`

Fuerza sincronización inmediata.

```javascript
const result = await syncService.syncNow();
console.log(`Sincronizadas ${result.count} operaciones`);
```

### Configuración

```javascript
const syncConfig = {
  syncInterval: 30000, // 30 segundos
  maxRetries: 3,
  batchSize: 50,
  conflictResolution: 'client-wins' // o 'server-wins', 'manual'
};

syncService.configure(syncConfig);
```

### Eventos

```javascript
syncService.on('syncStart', () => {
  console.log('Sincronización iniciada');
});

syncService.on('syncComplete', (result) => {
  console.log('Sincronización completada:', result);
});

syncService.on('syncError', (error) => {
  console.error('Error de sincronización:', error);
});

syncService.on('conflictDetected', (conflict) => {
  console.log('Conflicto detectado:', conflict);
});
```

## Export Service (exportService.js)

### Descripción

Servicio para exportación de datos en múltiples formatos.

### API Principal

#### `exportToJSON(data, filename)`

Exporta datos en formato JSON.

```javascript
import exportService from '@/services/exportService';

await exportService.exportToJSON(alarms, 'mis_alarmas.json');
```

#### `exportToCSV(data, filename, options)`

Exporta datos en formato CSV.

```javascript
await exportService.exportToCSV(alarms, 'alarmas.csv', {
  headers: ['Nombre', 'Fecha', 'Hora', 'Activa'],
  delimiter: ';'
});
```

#### `exportToPDF(data, template, filename)`

Exporta datos en formato PDF.

```javascript
await exportService.exportToPDF(report, 'report_template', 'reporte.pdf');
```

#### `importFromFile(file, format)`

Importa datos desde un archivo.

```javascript
const input = document.querySelector('input[type="file"]');
const data = await exportService.importFromFile(input.files[0], 'json');
```

## Mejores Prácticas

### 1. Manejo de Errores

```javascript
// Siempre envolver en try-catch
export const fetchUserData = async (userId) => {
  try {
    const response = await api.get(`/users/${userId}`);
    return response.data;
  } catch (error) {
    console.error('Error fetching user data:', error);

    // Manejo específico por tipo de error
    if (error.response?.status === 404) {
      throw new Error('Usuario no encontrado');
    }

    throw new Error('Error del servidor');
  }
};
```

### 2. Configuración Flexible

```javascript
class WeatherService {
  constructor(config = {}) {
    this.config = {
      apiKey: null,
      baseURL: 'https://api.openweathermap.org/data/2.5',
      timeout: 5000,
      ...config
    };
  }

  configure(newConfig) {
    this.config = { ...this.config, ...newConfig };
  }
}
```

### 3. Caching Inteligente

```javascript
class CachedService {
  constructor() {
    this.cache = new Map();
    this.cacheTimeout = 300000; // 5 minutos
  }

  async getData(key) {
    const cached = this.cache.get(key);

    if (cached && Date.now() - cached.timestamp < this.cacheTimeout) {
      return cached.data;
    }

    const data = await this.fetchData(key);
    this.cache.set(key, {
      data,
      timestamp: Date.now()
    });

    return data;
  }
}
```

### 4. Retry Logic

```javascript
const fetchWithRetry = async (url, options = {}, retries = 3) => {
  for (let i = 0; i < retries; i++) {
    try {
      return await fetch(url, options);
    } catch (error) {
      if (i === retries - 1) throw error;

      // Exponential backoff
      await new Promise(resolve =>
        setTimeout(resolve, Math.pow(2, i) * 1000)
      );
    }
  }
};
```

### 5. Event-Driven Architecture

```javascript
import EventEmitter from 'events';

class NotificationService extends EventEmitter {
  constructor() {
    super();
    this.notifications = [];
  }

  async sendNotification(notification) {
    try {
      await this.deliver(notification);
      this.emit('notificationSent', notification);
    } catch (error) {
      this.emit('notificationError', error, notification);
    }
  }
}
```

## Testing de Servicios

### Mocking de APIs

```javascript
import { describe, it, expect, vi } from 'vitest';
import api from '@/services/api';

// Mock del servicio
vi.mock('@/services/api');

describe('Weather Service', () => {
  it('should fetch weather data', async () => {
    const mockWeatherData = {
      temperature: 22,
      condition: 'sunny'
    };

    api.get.mockResolvedValue(mockWeatherData);

    const weather = await weatherService.getCurrentWeather(location);

    expect(api.get).toHaveBeenCalledWith('/weather', {
      params: { lat: location.latitude, lon: location.longitude }
    });
    expect(weather).toEqual(mockWeatherData);
  });
});
```

### Testing de Geolocalización

```javascript
describe('GeoAlarm Service', () => {
  beforeEach(() => {
    // Mock de geolocation API
    global.navigator.geolocation = {
      getCurrentPosition: vi.fn((success) => {
        success({
          coords: {
            latitude: 40.4168,
            longitude: -3.7038
          }
        });
      })
    };
  });

  it('should get current location', async () => {
    const location = await geoAlarmService.getCurrentLocation();

    expect(location.latitude).toBe(40.4168);
    expect(location.longitude).toBe(-3.7038);
  });
});
```