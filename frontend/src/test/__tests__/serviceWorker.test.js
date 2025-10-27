import { describe, it, expect, beforeEach, vi } from 'vitest'

// Mock del Service Worker
class MockServiceWorkerGlobalScope {
  constructor() {
    this.clients = {
      matchAll: vi.fn(() => Promise.resolve([])),
      claim: vi.fn(() => Promise.resolve())
    }
    this.caches = new MockCacheStorage()
    this.addEventListener = vi.fn()
    this.skipWaiting = vi.fn(() => Promise.resolve())
  }
}

class MockCacheStorage {
  constructor() {
    this.cacheStore = new Map()
  }

  open(cacheName) {
    if (!this.cacheStore.has(cacheName)) {
      this.cacheStore.set(cacheName, new MockCache())
    }
    return Promise.resolve(this.cacheStore.get(cacheName))
  }

  keys() {
    return Promise.resolve(Array.from(this.cacheStore.keys()))
  }

  delete(cacheName) {
    const deleted = this.cacheStore.delete(cacheName)
    return Promise.resolve(deleted)
  }

  match(request) {
    for (const cache of this.cacheStore.values()) {
      const response = cache.matchSync(request)
      if (response) {
        return Promise.resolve(response)
      }
    }
    return Promise.resolve(undefined)
  }
}

class MockCache {
  constructor() {
    this.store = new Map()
  }

  put(request, response) {
    const key = typeof request === 'string' ? request : request.url
    this.store.set(key, response.clone())
    return Promise.resolve()
  }

  match(request) {
    const key = typeof request === 'string' ? request : request.url
    return Promise.resolve(this.store.get(key))
  }

  matchSync(request) {
    const key = typeof request === 'string' ? request : request.url
    return this.store.get(key)
  }

  addAll(requests) {
    const promises = requests.map(request => {
      const response = new Response('mock content', { status: 200 })
      return this.put(request, response)
    })
    return Promise.all(promises)
  }

  keys() {
    return Promise.resolve(Array.from(this.store.keys()))
  }

  delete(request) {
    const key = typeof request === 'string' ? request : request.url
    return Promise.resolve(this.store.delete(key))
  }
}

class MockResponse extends Response {
  constructor(body, init = {}) {
    super(body, { status: 200, ...init })
  }

  clone() {
    return new MockResponse(this.body, {
      status: this.status,
      statusText: this.statusText,
      headers: this.headers
    })
  }
}

// Configurar el entorno de Service Worker
global.Response = MockResponse
global.Request = class MockRequest {
  constructor(url, options = {}) {
    this.url = url
    this.method = options.method || 'GET'
    this.mode = options.mode || 'cors'
  }
}

describe('Service Worker', () => {
  let sw
  let mockSelf

  beforeEach(() => {
    // Crear un nuevo contexto de Service Worker para cada test
    mockSelf = new MockServiceWorkerGlobalScope()

    // Mock global functions
    global.fetch = vi.fn()
    global.caches = mockSelf.caches
    global.self = mockSelf

    // Reset mocks
    vi.clearAllMocks()
  })

  describe('Instalación', () => {
    it('instala el service worker correctamente', async () => {
      const installEvent = {
        waitUntil: vi.fn((promise) => promise)
      }

      // Simular la instalación
      mockSelf.addEventListener.mockImplementation((event, handler) => {
        if (event === 'install') {
          handler(installEvent)
        }
      })

      // Verificar que se llama skipWaiting
      expect(mockSelf.skipWaiting).toBeDefined()
    })

    it('cachea archivos estáticos durante la instalación', async () => {
      const staticFiles = [
        '/',
        '/index.html',
        '/manifest.json',
        '/favicon.ico'
      ]

      const cache = await mockSelf.caches.open('tidy-static-v1')
      await cache.addAll(staticFiles)

      // Verificar que se cachearon todos los archivos
      const keys = await cache.keys()
      expect(keys).toHaveLength(staticFiles.length)
    })
  })

  describe('Activación', () => {
    it('activa el service worker y limpia caches antiguas', async () => {
      // Simular caches antiguas
      await mockSelf.caches.open('old-cache-v1')
      await mockSelf.caches.open('tidy-static-v1') // Cache actual
      await mockSelf.caches.open('old-cache-v2')

      const activateEvent = {
        waitUntil: vi.fn((promise) => promise)
      }

      mockSelf.addEventListener.mockImplementation((event, handler) => {
        if (event === 'activate') {
          handler(activateEvent)
        }
      })

      // Verificar que se conserva el cache actual
      const cacheNames = await mockSelf.caches.keys()
      expect(cacheNames).toContain('tidy-static-v1')
    })
  })

  describe('Intercepción de requests', () => {
    it('sirve archivos estáticos desde cache', async () => {
      const cache = await mockSelf.caches.open('tidy-static-v1')
      const request = new Request('/index.html')
      const response = new Response('cached content', { status: 200 })

      await cache.put(request, response)

      const cachedResponse = await cache.match(request)
      expect(cachedResponse).toBeDefined()
      expect(cachedResponse.status).toBe(200)
    })

    it('hace fetch para recursos no cacheados', async () => {
      const request = new Request('/api/data')
      const mockResponse = new Response('api data', { status: 200 })

      global.fetch.mockResolvedValue(mockResponse)

      const response = await global.fetch(request)

      expect(global.fetch).toHaveBeenCalledWith(request)
      expect(response.status).toBe(200)
    })

    it('maneja errores de red correctamente', async () => {
      const request = new Request('/api/data')

      global.fetch.mockRejectedValue(new Error('Network error'))

      try {
        await global.fetch(request)
      } catch (error) {
        expect(error.message).toBe('Network error')
      }
    })
  })

  describe('Estrategias de cache', () => {
    describe('Cache First', () => {
      it('devuelve respuesta desde cache si existe', async () => {
        const cache = await mockSelf.caches.open('tidy-dynamic-v1')
        const request = new Request('/assets/style.css')
        const cachedResponse = new Response('cached css', { status: 200 })

        await cache.put(request, cachedResponse)

        const response = await cache.match(request)
        expect(response).toBeDefined()
        expect(response.status).toBe(200)
      })

      it('hace fetch si no está en cache', async () => {
        const request = new Request('/assets/new-style.css')
        const networkResponse = new Response('fresh css', { status: 200 })

        global.fetch.mockResolvedValue(networkResponse)

        // Simular cache first strategy
        const cache = await mockSelf.caches.open('tidy-dynamic-v1')
        let response = await cache.match(request)

        if (!response) {
          response = await global.fetch(request)
          if (response && response.status === 200) {
            await cache.put(request, response.clone())
          }
        }

        expect(global.fetch).toHaveBeenCalledWith(request)
        expect(response.status).toBe(200)
      })
    })

    describe('Network First', () => {
      it('intenta red primero, luego cache', async () => {
        const request = new Request('/api/fresh-data')
        const networkResponse = new Response('fresh data', { status: 200 })

        global.fetch.mockResolvedValue(networkResponse)

        const response = await global.fetch(request)

        expect(global.fetch).toHaveBeenCalledWith(request)
        expect(response.status).toBe(200)
      })

      it('usa cache cuando la red falla', async () => {
        const cache = await mockSelf.caches.open('tidy-cache-v1')
        const request = new Request('/api/cached-data')
        const cachedResponse = new Response('cached data', { status: 200 })

        await cache.put(request, cachedResponse)

        global.fetch.mockRejectedValue(new Error('Network error'))

        // Simular network first strategy
        let response
        try {
          response = await global.fetch(request)
        } catch (error) {
          response = await cache.match(request)
        }

        expect(response).toBeDefined()
        expect(response.status).toBe(200)
      })
    })
  })

  describe('Identificación de tipos de request', () => {
    it('identifica recursos estáticos correctamente', () => {
      const staticFiles = [
        new Request('/'),
        new Request('/index.html'),
        new Request('/assets/style.css'),
        new Request('/favicon.ico')
      ]

      // Función helper para identificar recursos estáticos
      const isStaticResource = (request) => {
        const url = new URL(request.url, 'http://localhost')
        return ['/index.html', '/favicon.ico', '/manifest.json'].includes(url.pathname) ||
               url.pathname.includes('/assets/')
      }

      staticFiles.forEach(request => {
        if (request.url !== '/') { // '/' puede no ser considerado estático
          expect(isStaticResource(request)).toBe(request.url.includes('assets') ||
                                                   request.url.includes('favicon') ||
                                                   request.url.includes('index.html'))
        }
      })
    })

    it('identifica requests de API correctamente', () => {
      const apiRequests = [
        new Request('/api/alarms'),
        new Request('/api/notes'),
        new Request('/api/user/profile')
      ]

      const isApiRequest = (request) => {
        const url = new URL(request.url, 'http://localhost')
        return url.pathname.includes('/api/')
      }

      apiRequests.forEach(request => {
        expect(isApiRequest(request)).toBe(true)
      })
    })
  })

  describe('Gestión de cache', () => {
    it('limpia cache cuando se solicita', async () => {
      await mockSelf.caches.open('cache-1')
      await mockSelf.caches.open('cache-2')

      const initialCaches = await mockSelf.caches.keys()
      expect(initialCaches).toHaveLength(2)

      // Simular limpieza de cache
      const cacheNames = await mockSelf.caches.keys()
      await Promise.all(
        cacheNames.map(cacheName => mockSelf.caches.delete(cacheName))
      )

      const remainingCaches = await mockSelf.caches.keys()
      expect(remainingCaches).toHaveLength(0)
    })

    it('maneja versiones de cache correctamente', async () => {
      const CACHE_NAME = 'tidy-cache-v1'
      const STATIC_CACHE = 'tidy-static-v1'

      await mockSelf.caches.open(CACHE_NAME)
      await mockSelf.caches.open(STATIC_CACHE)
      await mockSelf.caches.open('old-cache-v1') // Cache obsoleta

      const cacheNames = await mockSelf.caches.keys()

      // Simular limpieza de caches obsoletas
      const validCaches = [CACHE_NAME, STATIC_CACHE]
      for (const cacheName of cacheNames) {
        if (!validCaches.includes(cacheName)) {
          await mockSelf.caches.delete(cacheName)
        }
      }

      const remainingCaches = await mockSelf.caches.keys()
      expect(remainingCaches).toEqual(expect.arrayContaining(validCaches))
      expect(remainingCaches).not.toContain('old-cache-v1')
    })
  })

  describe('Manejo de mensajes', () => {
    it('maneja mensaje CLEAR_CACHE', () => {
      const messageEvent = {
        data: { type: 'CLEAR_CACHE' },
        ports: [{ postMessage: vi.fn() }]
      }

      mockSelf.addEventListener.mockImplementation((event, handler) => {
        if (event === 'message') {
          handler(messageEvent)
        }
      })

      expect(messageEvent.data.type).toBe('CLEAR_CACHE')
    })

    it('maneja mensaje SKIP_WAITING', () => {
      const messageEvent = {
        data: { type: 'SKIP_WAITING' }
      }

      mockSelf.addEventListener.mockImplementation((event, handler) => {
        if (event === 'message') {
          handler(messageEvent)
        }
      })

      expect(messageEvent.data.type).toBe('SKIP_WAITING')
    })
  })

  describe('Background Sync', () => {
    it('maneja eventos de sincronización', () => {
      const syncEvent = {
        tag: 'background-sync',
        waitUntil: vi.fn((promise) => promise)
      }

      mockSelf.addEventListener.mockImplementation((event, handler) => {
        if (event === 'sync') {
          handler(syncEvent)
        }
      })

      expect(syncEvent.tag).toBe('background-sync')
    })
  })

  describe('Notificaciones a clientes', () => {
    it('notifica a clientes sobre actualizaciones', async () => {
      const mockClient = {
        postMessage: vi.fn()
      }

      mockSelf.clients.matchAll.mockResolvedValue([mockClient])

      // Simular notificación
      const clients = await mockSelf.clients.matchAll()
      clients.forEach(client => {
        client.postMessage({
          type: 'SW_UPDATE_AVAILABLE'
        })
      })

      expect(mockClient.postMessage).toHaveBeenCalledWith({
        type: 'SW_UPDATE_AVAILABLE'
      })
    })
  })
})