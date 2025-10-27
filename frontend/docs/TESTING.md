# Documentación de Testing - Proyecto Tidy

## Descripción General

El proyecto Tidy utiliza **Vitest** como framework principal de testing, complementado con **Vue Test Utils** para testing de componentes Vue. El sistema de testing está diseñado para ser rápido, confiable y fácil de mantener.

## Arquitectura de Testing

```
├── src/
│   ├── test/
│   │   ├── setup.js                 # Configuración global de tests
│   │   ├── helpers/                 # Utilidades de testing
│   │   │   ├── mockData.js         # Datos mock reutilizables
│   │   │   ├── testUtils.js        # Utilidades de testing
│   │   │   └── apiMocks.js         # Mocks de APIs
│   │   └── fixtures/               # Datos de prueba estáticos
│   ├── components/
│   │   └── **/*.test.js            # Tests de componentes
│   ├── stores/
│   │   └── **/*.test.js            # Tests de stores
│   ├── services/
│   │   └── **/*.test.js            # Tests de servicios
│   └── utils/
│       └── **/*.test.js            # Tests de utilidades
├── vitest.config.js                # Configuración de Vitest
└── coverage/                       # Reportes de cobertura
```

## Configuración de Vitest

### vitest.config.js

```javascript
import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import { quasar } from '@quasar/vite-plugin';
import path from 'path';

export default defineConfig({
  plugins: [
    vue(),
    quasar()
  ],

  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./src/test/setup.js'],
    coverage: {
      provider: 'c8',
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        'src/test/',
        '**/*.spec.js',
        '**/*.test.js',
        'src/main.js',
        'public/'
      ],
      thresholds: {
        global: {
          branches: 80,
          functions: 80,
          lines: 80,
          statements: 80
        }
      }
    },
    include: [
      'src/**/*.{test,spec}.{js,ts}'
    ]
  },

  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src')
    }
  }
});
```

### setup.js - Configuración Global

```javascript
import { config } from '@vue/test-utils';
import { Quasar, Notify } from 'quasar';
import { createI18n } from 'vue-i18n';
import { createTestingPinia } from '@pinia/testing';
import { vi } from 'vitest';

// Configurar Vue Test Utils globalmente
config.global.plugins = [
  [Quasar, {
    plugins: { Notify }
  }],
  createI18n({
    locale: 'es',
    fallbackLocale: 'es',
    messages: {
      es: {}
    }
  })
];

// Mock de APIs globales
global.localStorage = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn()
};

global.navigator.geolocation = {
  getCurrentPosition: vi.fn(),
  watchPosition: vi.fn(),
  clearWatch: vi.fn()
};

// Mock de Intersection Observer
global.IntersectionObserver = vi.fn(() => ({
  observe: vi.fn(),
  unobserve: vi.fn(),
  disconnect: vi.fn()
}));

// Mock de Service Worker
global.navigator.serviceWorker = {
  register: vi.fn(() => Promise.resolve({})),
  ready: Promise.resolve({})
};

// Suprimir warnings de testing
const originalWarn = console.warn;
console.warn = (...args) => {
  if (args[0]?.includes('Vue warn')) return;
  originalWarn(...args);
};
```

## Testing de Componentes Vue

### Ejemplo Básico - SimpleHome.test.js

```javascript
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { createTestingPinia } from '@pinia/testing';
import SimpleHome from '@/components/SimpleHome.vue';
import { useAuthStore } from '@/stores/auth';

describe('SimpleHome Component', () => {
  let wrapper;
  let mockRouter;

  beforeEach(() => {
    mockRouter = {
      push: vi.fn(),
      currentRoute: { value: { path: '/home' } }
    };

    wrapper = mount(SimpleHome, {
      global: {
        plugins: [
          createTestingPinia({
            createSpy: vi.fn,
            initialState: {
              auth: {
                user: { name: 'Test User' },
                isAuthenticated: true
              }
            }
          })
        ],
        mocks: {
          $router: mockRouter
        }
      }
    });
  });

  afterEach(() => {
    wrapper.unmount();
  });

  it('should render correctly', () => {
    expect(wrapper.exists()).toBe(true);
    expect(wrapper.find('h1').text()).toContain('Bienvenido');
  });

  it('should display user name', () => {
    const store = useAuthStore();
    expect(wrapper.text()).toContain('Test User');
  });

  it('should navigate on button click', async () => {
    const button = wrapper.find('[data-testid="nav-button"]');
    await button.trigger('click');

    expect(mockRouter.push).toHaveBeenCalledWith('/notes');
  });

  it('should logout user', async () => {
    const store = useAuthStore();
    const logoutButton = wrapper.find('[data-testid="logout-button"]');

    await logoutButton.trigger('click');

    expect(store.logout).toHaveBeenCalled();
  });
});
```

### Testing de Props y Eventos

```javascript
import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import LazyImage from '@/components/Common/LazyImage.vue';

describe('LazyImage Component', () => {
  it('should accept and display props correctly', () => {
    const wrapper = mount(LazyImage, {
      props: {
        src: '/test-image.jpg',
        alt: 'Test Image',
        width: 300,
        height: 200
      }
    });

    const img = wrapper.find('img');
    expect(img.attributes('alt')).toBe('Test Image');
    expect(wrapper.props('width')).toBe(300);
  });

  it('should emit load event', async () => {
    const wrapper = mount(LazyImage, {
      props: {
        src: '/test-image.jpg'
      }
    });

    const img = wrapper.find('img');
    await img.trigger('load');

    expect(wrapper.emitted('load')).toBeTruthy();
    expect(wrapper.emitted('load')).toHaveLength(1);
  });

  it('should emit error event on failed load', async () => {
    const wrapper = mount(LazyImage, {
      props: {
        src: '/invalid-image.jpg'
      }
    });

    const img = wrapper.find('img');
    await img.trigger('error');

    expect(wrapper.emitted('error')).toBeTruthy();
  });
});
```

### Testing de Slots

```javascript
describe('Modal Component Slots', () => {
  it('should render header slot content', () => {
    const wrapper = mount(Modal, {
      slots: {
        header: '<h2>Custom Header</h2>',
        default: '<p>Modal Content</p>',
        footer: '<button>Custom Button</button>'
      }
    });

    expect(wrapper.find('h2').text()).toBe('Custom Header');
    expect(wrapper.find('p').text()).toBe('Modal Content');
    expect(wrapper.find('button').text()).toBe('Custom Button');
  });
});
```

## Testing de Stores Pinia

### auth.test.js

```javascript
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useAuthStore } from '@/stores/auth';

// Mock del API service
vi.mock('@/services/api', () => ({
  default: {
    post: vi.fn(),
    get: vi.fn(),
    delete: vi.fn()
  }
}));

describe('Auth Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  describe('Initial State', () => {
    it('should have correct initial state', () => {
      const store = useAuthStore();

      expect(store.user).toBeNull();
      expect(store.token).toBeNull();
      expect(store.isAuthenticated).toBe(false);
      expect(store.loading).toBe(false);
    });
  });

  describe('Actions', () => {
    it('should login successfully', async () => {
      const store = useAuthStore();
      const mockResponse = {
        user: { id: 1, name: 'Test User' },
        token: 'mock-token'
      };

      const api = await import('@/services/api');
      api.default.post.mockResolvedValue(mockResponse);

      await store.login({
        email: 'test@example.com',
        password: 'password123'
      });

      expect(store.user).toEqual(mockResponse.user);
      expect(store.token).toBe(mockResponse.token);
      expect(store.isAuthenticated).toBe(true);
      expect(store.loading).toBe(false);
    });

    it('should handle login error', async () => {
      const store = useAuthStore();
      const mockError = new Error('Invalid credentials');

      const api = await import('@/services/api');
      api.default.post.mockRejectedValue(mockError);

      await expect(store.login({
        email: 'wrong@example.com',
        password: 'wrongpassword'
      })).rejects.toThrow('Invalid credentials');

      expect(store.user).toBeNull();
      expect(store.isAuthenticated).toBe(false);
    });

    it('should logout successfully', async () => {
      const store = useAuthStore();

      // Simular usuario logueado
      store.$patch({
        user: { id: 1, name: 'Test User' },
        token: 'mock-token',
        isAuthenticated: true
      });

      await store.logout();

      expect(store.user).toBeNull();
      expect(store.token).toBeNull();
      expect(store.isAuthenticated).toBe(false);
    });
  });

  describe('Getters', () => {
    it('should return correct user info', () => {
      const store = useAuthStore();
      store.$patch({
        user: { id: 1, name: 'Test User', email: 'test@example.com' }
      });

      expect(store.userName).toBe('Test User');
      expect(store.userEmail).toBe('test@example.com');
      expect(store.isLoggedIn).toBe(true);
    });
  });
});
```

### alarms.test.js

```javascript
import { describe, it, expect, vi, beforeEach } from 'vitest';
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
      time: '10:00',
      active: true
    };

    const api = await import('@/services/api');
    api.default.post.mockResolvedValue({
      id: 1,
      ...alarmData
    });

    await store.createAlarm(alarmData);

    expect(store.alarms).toHaveLength(1);
    expect(store.alarms[0].name).toBe('Test Alarm');
  });

  it('should filter active alarms', () => {
    const store = useAlarmsStore();
    store.$patch({
      alarms: [
        { id: 1, name: 'Active Alarm', active: true },
        { id: 2, name: 'Inactive Alarm', active: false }
      ]
    });

    const activeAlarms = store.activeAlarms;
    expect(activeAlarms).toHaveLength(1);
    expect(activeAlarms[0].name).toBe('Active Alarm');
  });
});
```

## Testing de Servicios

### geoAlarmService.test.js

```javascript
import { describe, it, expect, vi, beforeEach } from 'vitest';
import geoAlarmService from '@/services/geoAlarmService';

describe('GeoAlarm Service', () => {
  beforeEach(() => {
    // Mock de localStorage
    vi.clearAllMocks();

    // Mock de geolocation
    global.navigator.geolocation = {
      getCurrentPosition: vi.fn((success) => {
        success({
          coords: {
            latitude: 40.4168,
            longitude: -3.7038,
            accuracy: 10
          }
        });
      }),
      watchPosition: vi.fn(() => 1),
      clearWatch: vi.fn()
    };
  });

  describe('Location Management', () => {
    it('should get current location', async () => {
      const location = await geoAlarmService.getCurrentLocation();

      expect(location).toEqual({
        latitude: 40.4168,
        longitude: -3.7038,
        accuracy: 10
      });
    });

    it('should handle geolocation errors', async () => {
      global.navigator.geolocation.getCurrentPosition = vi.fn((success, error) => {
        error({
          code: 1,
          message: 'Permission denied'
        });
      });

      await expect(geoAlarmService.getCurrentLocation())
        .rejects.toThrow('Permission denied');
    });
  });

  describe('GeoAlarm Management', () => {
    it('should add geo alarm', async () => {
      const geoAlarm = {
        id: 'test-alarm',
        name: 'Test Alarm',
        latitude: 40.4168,
        longitude: -3.7038,
        radius: 100
      };

      await geoAlarmService.addGeoAlarm(geoAlarm);

      const alarms = geoAlarmService.getActiveGeoAlarms();
      expect(alarms).toHaveLength(1);
      expect(alarms[0].id).toBe('test-alarm');
    });

    it('should calculate distance correctly', () => {
      const point1 = { latitude: 40.4168, longitude: -3.7038 };
      const point2 = { latitude: 40.4170, longitude: -3.7040 };

      const distance = geoAlarmService.calculateDistance(point1, point2);

      expect(distance).toBeGreaterThan(0);
      expect(distance).toBeLessThan(50); // Aproximadamente 25 metros
    });

    it('should trigger alarm when entering zone', async () => {
      const geoAlarm = {
        id: 'test-alarm',
        latitude: 40.4168,
        longitude: -3.7038,
        radius: 100,
        triggerOnEnter: true
      };

      await geoAlarmService.addGeoAlarm(geoAlarm);

      const mockCallback = vi.fn();
      geoAlarmService.on('geoAlarmTriggered', mockCallback);

      // Simular ubicación dentro del radio
      await geoAlarmService.checkProximity({
        latitude: 40.4169,
        longitude: -3.7039
      });

      expect(mockCallback).toHaveBeenCalledWith(
        expect.objectContaining({ id: 'test-alarm' })
      );
    });
  });

  describe('Persistence', () => {
    it('should save geo alarms to localStorage', async () => {
      const geoAlarm = {
        id: 'persistent-alarm',
        name: 'Persistent Alarm',
        latitude: 40.4168,
        longitude: -3.7038,
        radius: 100
      };

      await geoAlarmService.addGeoAlarm(geoAlarm);

      expect(localStorage.setItem).toHaveBeenCalledWith(
        'geoAlarms',
        expect.stringContaining('persistent-alarm')
      );
    });

    it('should load geo alarms from localStorage', () => {
      const savedAlarms = JSON.stringify([{
        id: 'saved-alarm',
        name: 'Saved Alarm',
        latitude: 40.4168,
        longitude: -3.7038,
        radius: 100
      }]);

      localStorage.getItem.mockReturnValue(savedAlarms);

      geoAlarmService.loadGeoAlarms();

      const alarms = geoAlarmService.getActiveGeoAlarms();
      expect(alarms).toHaveLength(1);
      expect(alarms[0].id).toBe('saved-alarm');
    });
  });
});
```

## Testing de Composables

### useImageOptimization.test.js

```javascript
import { describe, it, expect, vi } from 'vitest';
import { useImageOptimization } from '@/composables/useImageOptimization';

describe('useImageOptimization', () => {
  it('should preload images successfully', async () => {
    const { preloadImages } = useImageOptimization();

    // Mock de Image constructor
    const mockImage = {
      onload: null,
      onerror: null,
      src: ''
    };

    global.Image = vi.fn(() => mockImage);

    const imageUrls = ['/image1.jpg', '/image2.jpg'];
    const preloadPromise = preloadImages(imageUrls);

    // Simular carga exitosa
    setTimeout(() => {
      mockImage.onload();
    }, 0);

    const results = await preloadPromise;

    expect(results).toHaveLength(2);
    expect(results.every(result => result.status === 'fulfilled')).toBe(true);
  });

  it('should check WebP support', () => {
    const { checkWebPSupport } = useImageOptimization();

    // Mock de canvas y toDataURL
    const mockCanvas = {
      toDataURL: vi.fn(() => 'data:image/webp;base64,mock')
    };

    global.document.createElement = vi.fn(() => mockCanvas);

    const isSupported = checkWebPSupport();
    expect(isSupported).toBe(true);
  });
});
```

## Testing de PWA y Service Workers

### serviceWorker.test.js

```javascript
import { describe, it, expect, vi, beforeEach } from 'vitest';

describe('Service Worker', () => {
  beforeEach(() => {
    // Mock de Service Worker APIs
    global.self = {
      addEventListener: vi.fn(),
      clients: {
        claim: vi.fn(),
        matchAll: vi.fn(() => Promise.resolve([]))
      },
      registration: {
        showNotification: vi.fn()
      }
    };

    global.caches = {
      open: vi.fn(() => Promise.resolve({
        match: vi.fn(),
        put: vi.fn(),
        addAll: vi.fn()
      })),
      match: vi.fn()
    };
  });

  it('should install and cache resources', async () => {
    const { installServiceWorker } = await import('@/sw.js');

    const mockCache = {
      addAll: vi.fn(() => Promise.resolve())
    };

    global.caches.open.mockResolvedValue(mockCache);

    await installServiceWorker();

    expect(global.caches.open).toHaveBeenCalledWith('tidy-cache-v1');
    expect(mockCache.addAll).toHaveBeenCalled();
  });

  it('should handle fetch events with cache strategy', async () => {
    const { handleFetch } = await import('@/sw.js');

    const mockRequest = new Request('/api/alarms');
    const mockResponse = new Response('{}');

    global.caches.match.mockResolvedValue(mockResponse);

    const response = await handleFetch({ request: mockRequest });

    expect(response).toBe(mockResponse);
  });
});
```

## Testing de Integración

### apiIntegration.test.js

```javascript
import { describe, it, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { createTestingPinia } from '@pinia/testing';
import AlarmsList from '@/components/Elements/Alarms/AlarmsList.vue';
import { useAlarmsStore } from '@/stores/alarms';

describe('Alarms Integration', () => {
  beforeEach(() => {
    // Setup para tests de integración
  });

  it('should create, update and delete alarm flow', async () => {
    const wrapper = mount(AlarmsList, {
      global: {
        plugins: [createTestingPinia({ createSpy: vi.fn })]
      }
    });

    const store = useAlarmsStore();

    // Test de creación
    const createButton = wrapper.find('[data-testid="create-alarm"]');
    await createButton.trigger('click');

    const nameInput = wrapper.find('[data-testid="alarm-name"]');
    await nameInput.setValue('Integration Test Alarm');

    const saveButton = wrapper.find('[data-testid="save-alarm"]');
    await saveButton.trigger('click');

    expect(store.createAlarm).toHaveBeenCalledWith(
      expect.objectContaining({
        name: 'Integration Test Alarm'
      })
    );

    // Test de actualización
    store.$patch({
      alarms: [{ id: 1, name: 'Integration Test Alarm', active: true }]
    });

    await wrapper.vm.$nextTick();

    const editButton = wrapper.find('[data-testid="edit-alarm-1"]');
    await editButton.trigger('click');

    const updateNameInput = wrapper.find('[data-testid="alarm-name"]');
    await updateNameInput.setValue('Updated Alarm Name');

    const updateButton = wrapper.find('[data-testid="save-alarm"]');
    await updateButton.trigger('click');

    expect(store.updateAlarm).toHaveBeenCalledWith(1, {
      name: 'Updated Alarm Name'
    });

    // Test de eliminación
    const deleteButton = wrapper.find('[data-testid="delete-alarm-1"]');
    await deleteButton.trigger('click');

    expect(store.deleteAlarm).toHaveBeenCalledWith(1);
  });
});
```

## Utilidades de Testing

### testUtils.js

```javascript
import { mount } from '@vue/test-utils';
import { createTestingPinia } from '@pinia/testing';
import { createI18n } from 'vue-i18n';
import { Quasar } from 'quasar';

/**
 * Factory para crear wrapper de componente con configuración común
 */
export const createWrapper = (component, options = {}) => {
  const defaultOptions = {
    global: {
      plugins: [
        [Quasar],
        createTestingPinia({ createSpy: vi.fn }),
        createI18n({
          locale: 'es',
          messages: { es: {} }
        })
      ],
      mocks: {
        $t: (key) => key,
        $router: { push: vi.fn() },
        $route: { path: '/' }
      }
    }
  };

  const mergedOptions = {
    ...defaultOptions,
    ...options,
    global: {
      ...defaultOptions.global,
      ...options.global
    }
  };

  return mount(component, mergedOptions);
};

/**
 * Helper para esperar que un elemento aparezca en el DOM
 */
export const waitForElement = async (wrapper, selector, timeout = 1000) => {
  const start = Date.now();

  while (Date.now() - start < timeout) {
    await wrapper.vm.$nextTick();

    if (wrapper.find(selector).exists()) {
      return wrapper.find(selector);
    }

    await new Promise(resolve => setTimeout(resolve, 10));
  }

  throw new Error(`Element ${selector} not found within ${timeout}ms`);
};

/**
 * Helper para simular entrada de usuario en inputs
 */
export const fillForm = async (wrapper, formData) => {
  for (const [selector, value] of Object.entries(formData)) {
    const input = wrapper.find(selector);
    if (input.exists()) {
      await input.setValue(value);
    }
  }

  await wrapper.vm.$nextTick();
};

/**
 * Helper para aserciones de accesibilidad básicas
 */
export const checkAccessibility = (wrapper) => {
  // Verificar que los botones tengan labels
  const buttons = wrapper.findAll('button');
  buttons.forEach(button => {
    const hasLabel = button.attributes('aria-label') ||
                    button.text().trim() ||
                    button.find('span').text().trim();
    expect(hasLabel).toBeTruthy();
  });

  // Verificar que las imágenes tengan alt text
  const images = wrapper.findAll('img');
  images.forEach(img => {
    expect(img.attributes('alt')).toBeDefined();
  });
};
```

### mockData.js

```javascript
export const mockUser = {
  id: 1,
  name: 'Test User',
  email: 'test@example.com',
  role: 'user',
  avatar: '/avatars/test.jpg',
  settings: {
    theme: 'light',
    language: 'es',
    notifications: true
  }
};

export const mockAlarms = [
  {
    id: 1,
    name: 'Morning Meeting',
    date: '2024-01-15',
    time: '09:00',
    active: true,
    gps: {
      enabled: true,
      latitude: 40.4168,
      longitude: -3.7038,
      radius: 100
    }
  },
  {
    id: 2,
    name: 'Lunch Break',
    date: '2024-01-15',
    time: '13:00',
    active: false,
    weather: {
      enabled: true,
      conditions: ['sunny']
    }
  }
];

export const mockNotes = [
  {
    id: 1,
    title: 'Shopping List',
    content: 'Milk, Bread, Eggs',
    type: 'shopping',
    tags: ['groceries'],
    archived: false,
    createdAt: '2024-01-01T00:00:00Z'
  },
  {
    id: 2,
    title: 'Meeting Notes',
    content: 'Discussed project timeline',
    type: 'normal',
    tags: ['work', 'meetings'],
    archived: false,
    createdAt: '2024-01-02T00:00:00Z'
  }
];
```

## Scripts de Testing

### package.json

```json
{
  "scripts": {
    "test": "vitest",
    "test:run": "vitest run",
    "test:watch": "vitest --watch",
    "test:coverage": "vitest run --coverage",
    "test:ui": "vitest --ui",
    "test:components": "vitest run src/components",
    "test:stores": "vitest run src/stores",
    "test:services": "vitest run src/services",
    "test:e2e": "cypress run",
    "test:e2e:open": "cypress open"
  }
}
```

## Configuración de CI/CD

### GitHub Actions - .github/workflows/test.yml

```yaml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v3

    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
        cache: 'npm'

    - name: Install dependencies
      run: npm ci

    - name: Run tests
      run: npm run test:run

    - name: Generate coverage
      run: npm run test:coverage

    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage/lcov.info
```

## Mejores Prácticas

### 1. Estructura de Tests

```javascript
describe('Component/Service Name', () => {
  // Setup común
  beforeEach(() => {
    // Configuración antes de cada test
  });

  describe('Feature Group', () => {
    it('should do specific thing', () => {
      // Test específico
    });
  });

  describe('Error Handling', () => {
    it('should handle error gracefully', () => {
      // Test de manejo de errores
    });
  });
});
```

### 2. Naming Conventions

- Tests descriptivos: `should create alarm when valid data provided`
- Agrupación lógica: `describe('Authentication', ...)`
- Data tests: `mockUser`, `mockAlarms`
- Test IDs: `data-testid="create-alarm-button"`

### 3. Mocking Strategy

```javascript
// Mock a nivel de módulo
vi.mock('@/services/api');

// Mock específico para test
const mockFunction = vi.fn().mockResolvedValue(mockData);

// Restaurar mocks
afterEach(() => {
  vi.clearAllMocks();
});
```

### 4. Async Testing

```javascript
// Testing de promesas
await expect(asyncFunction()).resolves.toBe(expectedValue);
await expect(asyncFunction()).rejects.toThrow('Error message');

// Testing de eventos async
await wrapper.vm.$nextTick();
await new Promise(resolve => setTimeout(resolve, 100));
```

### 5. Coverage Goals

- **Statements**: 80%+
- **Branches**: 80%+
- **Functions**: 80%+
- **Lines**: 80%+

Priorizar cobertura de:
- Lógica de negocio crítica
- Manejo de errores
- Flujos de usuario principales
- APIs y servicios