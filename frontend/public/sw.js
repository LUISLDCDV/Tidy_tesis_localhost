// Service Worker para cache de la aplicación Tidy
const CACHE_NAME = 'tidy-cache-v1';
const STATIC_CACHE = 'tidy-static-v1';
const DYNAMIC_CACHE = 'tidy-dynamic-v1';

// Archivos que se cachean inmediatamente
const STATIC_FILES = [
  '/',
  '/index.html',
  '/manifest.json',
  '/favicon.ico'
];

// Recursos que se cachean dinámicamente
const DYNAMIC_PATTERNS = [
  /\/assets\//,
  /\.js$/,
  /\.css$/,
  /\.woff2?$/,
  /\.png$/,
  /\.jpg$/,
  /\.svg$/
];

// URLs de APIs que se cachean
const API_CACHE_PATTERNS = [
  /\/api\/alarms/,
  /\/api\/notes/,
  /\/api\/calendars/,
  /\/api\/objectives/
];

// Instalar Service Worker
self.addEventListener('install', (event) => {
  console.log('SW: Installing Service Worker');

  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('SW: Caching static assets');
        return cache.addAll(STATIC_FILES);
      })
      .then(() => {
        return self.skipWaiting();
      })
  );
});

// Activar Service Worker
self.addEventListener('activate', (event) => {
  console.log('SW: Activating Service Worker');

  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== STATIC_CACHE &&
                cacheName !== DYNAMIC_CACHE &&
                cacheName !== CACHE_NAME) {
              console.log('SW: Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        return self.clients.claim();
      })
  );
});

// Interceptar requests
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Ignorar requests de chrome-extension y otros protocolos no HTTP
  if (!request.url.startsWith('http')) {
    return;
  }

  // Estrategia Cache First para recursos estáticos
  if (isStaticResource(request)) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // Estrategia Network First para APIs con fallback
  if (isApiRequest(request)) {
    event.respondWith(networkFirstWithCache(request));
    return;
  }

  // Estrategia Cache First para recursos dinámicos
  if (isDynamicResource(request)) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // Default: intentar red primero, luego cache
  event.respondWith(networkFirst(request));
});

// Estrategia Cache First
async function cacheFirst(request) {
  try {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }

    const networkResponse = await fetch(request);

    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }

    return networkResponse;
  } catch (error) {
    console.log('SW: Cache first failed:', error);

    // Fallback para navegación
    if (request.mode === 'navigate') {
      return caches.match('/index.html');
    }

    throw error;
  }
}

// Estrategia Network First
async function networkFirst(request) {
  try {
    const networkResponse = await fetch(request);

    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }

    return networkResponse;
  } catch (error) {
    console.log('SW: Network first failed, trying cache:', error);

    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }

    // Fallback para navegación
    if (request.mode === 'navigate') {
      return caches.match('/index.html');
    }

    throw error;
  }
}

// Estrategia Network First con cache especial para APIs
async function networkFirstWithCache(request) {
  try {
    const networkResponse = await fetch(request);

    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(CACHE_NAME);
      // Solo cachear respuestas GET exitosas
      if (request.method === 'GET') {
        cache.put(request, networkResponse.clone());
      }
    }

    return networkResponse;
  } catch (error) {
    console.log('SW: Network failed for API, trying cache:', error);

    // Solo usar cache para requests GET
    if (request.method === 'GET') {
      const cachedResponse = await caches.match(request);
      if (cachedResponse) {
        return cachedResponse;
      }
    }

    // Para métodos POST/PUT/DELETE, devolver error de red
    throw error;
  }
}

// Helpers para identificar tipos de request
function isStaticResource(request) {
  const url = new URL(request.url);
  return STATIC_FILES.some(file => url.pathname === file) ||
         url.pathname.includes('/assets/') ||
         url.pathname.includes('/favicon.ico') ||
         url.pathname.includes('/manifest.json');
}

function isDynamicResource(request) {
  const url = new URL(request.url);
  return DYNAMIC_PATTERNS.some(pattern => pattern.test(url.pathname));
}

function isApiRequest(request) {
  const url = new URL(request.url);
  return API_CACHE_PATTERNS.some(pattern => pattern.test(url.pathname)) ||
         url.pathname.includes('/api/');
}

// Limpiar cache cuando se necesite
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    caches.keys().then((cacheNames) => {
      Promise.all(
        cacheNames.map((cacheName) => caches.delete(cacheName))
      ).then(() => {
        event.ports[0].postMessage({ success: true });
      });
    });
  }

  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

// Notificar cuando hay una nueva versión disponible
self.addEventListener('updatefound', () => {
  console.log('SW: New version available');

  // Notificar a la aplicación
  self.clients.matchAll().then((clients) => {
    clients.forEach((client) => {
      client.postMessage({
        type: 'SW_UPDATE_AVAILABLE'
      });
    });
  });
});

// Manejo de sincronización en background (para funcionalidad offline)
self.addEventListener('sync', (event) => {
  if (event.tag === 'background-sync') {
    console.log('SW: Background sync triggered');
    event.waitUntil(doBackgroundSync());
  }
});

async function doBackgroundSync() {
  // Aquí se pueden sincronizar datos pendientes cuando se recupere la conexión
  console.log('SW: Performing background sync');

  try {
    // Enviar notificación a la app sobre la sincronización
    const clients = await self.clients.matchAll();
    clients.forEach((client) => {
      client.postMessage({
        type: 'BACKGROUND_SYNC_COMPLETE'
      });
    });
  } catch (error) {
    console.error('SW: Background sync failed:', error);
  }
}

console.log('SW: Service Worker loaded');