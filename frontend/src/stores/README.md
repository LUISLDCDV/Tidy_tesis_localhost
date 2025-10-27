# Stores de Pinia - Documentación

## Descripción General

La aplicación Tidy utiliza Pinia como solución de gestión de estado. Los stores están organizados por funcionalidad y proporcionan una API reactiva para el manejo de datos.

## Arquitectura de Stores

```
stores/
├── auth.js              # Autenticación y usuario
├── alarms.js            # Gestión de alarmas
├── notes.js             # Gestión de notas
├── objectives.js        # Gestión de objetivos
├── calendars.js         # Gestión de calendarios
├── config.js            # Configuración global
├── notifications.js     # Sistema de notificaciones
├── userSettings.js      # Configuraciones de usuario
└── index.js            # Configuración de Pinia
```

## Store de Autenticación (auth.js)

### Estado

```javascript
{
  user: null,                    // Usuario actual
  token: null,                   // Token de autenticación
  isAuthenticated: false,        // Estado de autenticación
  loading: false,                // Estado de carga
  lastActivity: null             // Última actividad del usuario
}
```

### Acciones Principales

#### `login(credentials)`
Autentica al usuario con credenciales.

```javascript
const authStore = useAuthStore();
await authStore.login({
  email: 'usuario@ejemplo.com',
  password: 'contraseña123'
});
```

#### `logout()`
Cierra la sesión del usuario.

```javascript
await authStore.logout();
```

#### `refreshToken()`
Renueva el token de autenticación.

```javascript
await authStore.refreshToken();
```

#### `updateProfile(profileData)`
Actualiza el perfil del usuario.

```javascript
await authStore.updateProfile({
  name: 'Nuevo Nombre',
  email: 'nuevo@email.com'
});
```

### Getters

- `isLoggedIn`: Verifica si el usuario está autenticado
- `userRole`: Obtiene el rol del usuario
- `userName`: Obtiene el nombre del usuario
- `userEmail`: Obtiene el email del usuario

## Store de Alarmas (alarms.js)

### Estado

```javascript
{
  alarms: [],                    // Lista de alarmas
  loading: false,                // Estado de carga
  error: null,                   // Error actual
  filters: {                     // Filtros activos
    active: true,
    type: null,
    search: ''
  },
  sortBy: 'date',               // Ordenamiento
  sortOrder: 'asc'              // Dirección del ordenamiento
}
```

### Acciones Principales

#### `fetchAlarms()`
Obtiene todas las alarmas del usuario.

```javascript
const alarmsStore = useAlarmsStore();
await alarmsStore.fetchAlarms();
```

#### `createAlarm(alarmData)`
Crea una nueva alarma.

```javascript
await alarmsStore.createAlarm({
  name: 'Reunión importante',
  date: '2024-01-15',
  time: '10:00',
  gps: {
    enabled: true,
    latitude: 40.4168,
    longitude: -3.7038,
    radius: 100
  },
  weather: {
    enabled: true,
    conditions: ['sunny', 'cloudy']
  }
});
```

#### `updateAlarm(id, updates)`
Actualiza una alarma existente.

```javascript
await alarmsStore.updateAlarm(alarmId, {
  name: 'Nuevo nombre',
  active: false
});
```

#### `deleteAlarm(id)`
Elimina una alarma.

```javascript
await alarmsStore.deleteAlarm(alarmId);
```

#### `toggleAlarm(id)`
Activa/desactiva una alarma.

```javascript
await alarmsStore.toggleAlarm(alarmId);
```

### Getters

- `activeAlarms`: Alarmas activas
- `inactiveAlarms`: Alarmas inactivas
- `filteredAlarms`: Alarmas filtradas
- `upcomingAlarms`: Próximas alarmas
- `overdueAlarms`: Alarmas vencidas
- `gpsAlarms`: Alarmas con GPS
- `weatherAlarms`: Alarmas con clima

## Store de Notas (notes.js)

### Estado

```javascript
{
  notes: [],                     // Lista de notas
  archivedNotes: [],            // Notas archivadas
  currentNote: null,            // Nota actual en edición
  loading: false,               // Estado de carga
  autoSave: true,               // Auto-guardado activado
  lastSaved: null              // Última vez guardado
}
```

### Acciones Principales

#### `fetchNotes()`
Obtiene todas las notas.

```javascript
const notesStore = useNotesStore();
await notesStore.fetchNotes();
```

#### `createNote(noteData)`
Crea una nueva nota.

```javascript
await notesStore.createNote({
  title: 'Mi nueva nota',
  content: 'Contenido de la nota',
  type: 'normal',
  tags: ['importante', 'trabajo']
});
```

#### `updateNote(id, updates)`
Actualiza una nota.

```javascript
await notesStore.updateNote(noteId, {
  title: 'Título actualizado',
  content: 'Nuevo contenido'
});
```

#### `archiveNote(id)`
Archiva una nota.

```javascript
await notesStore.archiveNote(noteId);
```

#### `autoSaveNote(id, content)`
Auto-guarda una nota mientras se edita.

```javascript
await notesStore.autoSaveNote(noteId, newContent);
```

### Getters

- `activeNotes`: Notas activas
- `notesByType`: Notas agrupadas por tipo
- `searchNotes`: Buscar notas
- `recentNotes`: Notas recientes
- `favoriteNotes`: Notas favoritas

## Store de Objetivos (objectives.js)

### Estado

```javascript
{
  objectives: [],               // Lista de objetivos
  steps: [],                   // Pasos de objetivos
  loading: false,              // Estado de carga
  progress: {},                // Progreso por objetivo
  categories: []               // Categorías de objetivos
}
```

### Acciones Principales

#### `fetchObjectives()`
Obtiene todos los objetivos.

#### `createObjective(objectiveData)`
Crea un nuevo objetivo.

#### `updateProgress(objectiveId, stepId, completed)`
Actualiza el progreso de un objetivo.

#### `addStep(objectiveId, stepData)`
Agrega un paso a un objetivo.

### Getters

- `activeObjectives`: Objetivos activos
- `completedObjectives`: Objetivos completados
- `objectiveProgress`: Progreso de objetivos
- `overallProgress`: Progreso general

## Store de Configuración (config.js)

### Estado

```javascript
{
  theme: 'auto',               // Tema de la aplicación
  language: 'es',              // Idioma
  timezone: 'Europe/Madrid',   // Zona horaria
  notifications: true,         // Notificaciones activadas
  autoSync: true,             // Sincronización automática
  debugMode: false            // Modo debug
}
```

### Acciones Principales

#### `initTheme()`
Inicializa el tema de la aplicación.

#### `setLanguage(lang)`
Cambia el idioma de la aplicación.

#### `toggleTheme()`
Alterna entre tema claro y oscuro.

#### `loadStorageSettings()`
Carga configuraciones desde localStorage.

## Store de Notificaciones (notifications.js)

### Estado

```javascript
{
  notifications: [],           // Lista de notificaciones
  unreadCount: 0,             // Notificaciones no leídas
  settings: {                 // Configuraciones
    push: true,
    email: false,
    sound: true
  }
}
```

### Acciones Principales

#### `fetchNotifications()`
Obtiene todas las notificaciones.

#### `markAsRead(id)`
Marca una notificación como leída.

#### `clearAll()`
Elimina todas las notificaciones.

#### `updateSettings(settings)`
Actualiza configuraciones de notificaciones.

## Uso de Stores en Componentes

### Composition API

```javascript
import { useAuthStore } from '@/stores/auth';
import { useAlarmsStore } from '@/stores/alarms';

export default {
  setup() {
    const authStore = useAuthStore();
    const alarmsStore = useAlarmsStore();

    // Estado reactivo
    const { user, isAuthenticated } = storeToRefs(authStore);
    const { alarms, loading } = storeToRefs(alarmsStore);

    // Acciones
    const login = authStore.login;
    const fetchAlarms = alarmsStore.fetchAlarms;

    return {
      user,
      isAuthenticated,
      alarms,
      loading,
      login,
      fetchAlarms
    };
  }
};
```

### Options API

```javascript
import { mapStores, mapState, mapActions } from 'pinia';
import { useAuthStore } from '@/stores/auth';

export default {
  computed: {
    ...mapStores(useAuthStore),
    ...mapState(useAuthStore, ['user', 'isAuthenticated'])
  },

  methods: {
    ...mapActions(useAuthStore, ['login', 'logout'])
  }
};
```

## Persistencia de Datos

### Configuración de Persistencia

```javascript
// stores/auth.js
export const useAuthStore = defineStore('auth', {
  state: () => ({ /* ... */ }),

  persist: {
    key: 'tidy_auth',
    storage: localStorage,
    paths: ['token', 'user', 'isAuthenticated']
  }
});
```

### Sincronización con Backend

```javascript
// Middleware para sincronización automática
export const syncMiddleware = {
  onAction: ({ name, store, args, after }) => {
    after((result) => {
      if (name.includes('create') || name.includes('update') || name.includes('delete')) {
        // Sincronizar con backend
        store.sync();
      }
    });
  }
};
```

## Testing de Stores

### Test de Store de Alarmas

```javascript
import { describe, it, expect, beforeEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useAlarmsStore } from '@/stores/alarms';

describe('Alarms Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it('should create an alarm', async () => {
    const store = useAlarmsStore();

    const alarmData = {
      name: 'Test Alarm',
      date: '2024-01-01',
      time: '10:00'
    };

    await store.createAlarm(alarmData);

    expect(store.alarms).toHaveLength(1);
    expect(store.alarms[0].name).toBe('Test Alarm');
  });
});
```

## Mejores Prácticas

### 1. Nomenclatura Consistente

- Usar verbos para acciones: `fetchAlarms`, `createAlarm`
- Usar sustantivos para getters: `activeAlarms`, `userProfile`
- Prefijos para estados: `loading`, `error`, `success`

### 2. Gestión de Errores

```javascript
export const useAlarmsStore = defineStore('alarms', {
  state: () => ({
    error: null,
    loading: false
  }),

  actions: {
    async fetchAlarms() {
      this.loading = true;
      this.error = null;

      try {
        const response = await api.get('/alarms');
        this.alarms = response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Error fetching alarms:', error);
      } finally {
        this.loading = false;
      }
    }
  }
});
```

### 3. Optimización de Performance

```javascript
// Usar computed para cálculos complejos
export const useAlarmsStore = defineStore('alarms', {
  getters: {
    filteredAlarms: (state) => {
      return computed(() => {
        return state.alarms.filter(alarm => {
          // Lógica de filtrado optimizada
          return alarm.active && alarm.name.includes(state.searchTerm);
        });
      });
    }
  }
});
```

### 4. Composición de Stores

```javascript
// Usar múltiples stores en un composable
export function useAlarmsManagement() {
  const alarmsStore = useAlarmsStore();
  const notificationsStore = useNotificationsStore();
  const configStore = useConfigStore();

  const createAlarmWithNotification = async (alarmData) => {
    const alarm = await alarmsStore.createAlarm(alarmData);

    if (configStore.notifications) {
      await notificationsStore.scheduleNotification(alarm);
    }

    return alarm;
  };

  return {
    createAlarmWithNotification
  };
}
```

## Debugging

### DevTools

Para debugging en desarrollo, usar las DevTools de Pinia:

```javascript
// main.js
if (process.env.NODE_ENV === 'development') {
  pinia.use(({ store }) => {
    store.$subscribe((mutation, state) => {
      console.log('Store mutation:', mutation);
      console.log('New state:', state);
    });
  });
}
```

### Logging Personalizado

```javascript
// stores/alarms.js
export const useAlarmsStore = defineStore('alarms', {
  actions: {
    async createAlarm(alarmData) {
      console.log('Creating alarm:', alarmData);

      try {
        const result = await api.post('/alarms', alarmData);
        console.log('Alarm created successfully:', result);
        return result;
      } catch (error) {
        console.error('Failed to create alarm:', error);
        throw error;
      }
    }
  }
});
```

## Migración de Vuex a Pinia

Si estás migrando desde Vuex, aquí tienes las equivalencias:

| Vuex | Pinia |
|------|-------|
| `state` | `state` |
| `getters` | `getters` |
| `mutations` | Se eliminan, usar acciones directas |
| `actions` | `actions` |
| `modules` | Stores separados |
| `commit` | Llamada directa a acción |
| `dispatch` | Llamada directa a acción |