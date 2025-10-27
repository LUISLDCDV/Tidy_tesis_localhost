import { vi } from 'vitest'

// Mock localStorage
const localStorageMock = {
  getItem: vi.fn((key) => {
    return localStorageMock.store[key] || null
  }),
  setItem: vi.fn((key, value) => {
    localStorageMock.store[key] = String(value)
  }),
  removeItem: vi.fn((key) => {
    delete localStorageMock.store[key]
  }),
  clear: vi.fn(() => {
    localStorageMock.store = {}
  }),
  store: {}
}

global.localStorage = localStorageMock

// Mock sessionStorage
const sessionStorageMock = {
  getItem: vi.fn((key) => {
    return sessionStorageMock.store[key] || null
  }),
  setItem: vi.fn((key, value) => {
    sessionStorageMock.store[key] = String(value)
  }),
  removeItem: vi.fn((key) => {
    delete sessionStorageMock.store[key]
  }),
  clear: vi.fn(() => {
    sessionStorageMock.store = {}
  }),
  store: {}
}

global.sessionStorage = sessionStorageMock

// Mock navigator
global.navigator = {
  onLine: true,
  geolocation: {
    getCurrentPosition: vi.fn((success) => {
      success({
        coords: {
          latitude: 40.7128,
          longitude: -74.0060,
          accuracy: 10
        }
      })
    }),
    watchPosition: vi.fn(() => 1),
    clearWatch: vi.fn()
  },
  permissions: {
    query: vi.fn(() => Promise.resolve({ state: 'granted' }))
  }
}

// Mock window.Notification
class MockNotification {
  constructor(title, options) {
    this.title = title
    this.options = options
  }
}
MockNotification.permission = 'granted'
MockNotification.requestPermission = vi.fn(() => Promise.resolve('granted'))

global.Notification = MockNotification
window.Notification = MockNotification

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
})

// Mock indexedDB
const indexedDBMock = {
  open: vi.fn(() => {
    const request = {
      result: {
        objectStoreNames: { contains: vi.fn(() => false) },
        createObjectStore: vi.fn(() => ({
          createIndex: vi.fn()
        })),
        transaction: vi.fn(() => ({
          objectStore: vi.fn(() => ({
            get: vi.fn(() => ({ onsuccess: null, onerror: null })),
            put: vi.fn(() => ({ onsuccess: null, onerror: null })),
            delete: vi.fn(() => ({ onsuccess: null, onerror: null })),
            clear: vi.fn(() => ({ onsuccess: null, onerror: null })),
            getAll: vi.fn(() => ({ onsuccess: null, onerror: null }))
          }))
        }))
      },
      onsuccess: null,
      onerror: null,
      onupgradeneeded: null
    }

    // Simulate successful connection
    setTimeout(() => {
      if (request.onupgradeneeded) {
        request.onupgradeneeded({ target: request })
      }
      if (request.onsuccess) {
        request.onsuccess({ target: request })
      }
    }, 0)

    return request
  }),
  deleteDatabase: vi.fn()
}

global.indexedDB = indexedDBMock
window.indexedDB = indexedDBMock

// Reset all mocks before each test
beforeEach(() => {
  localStorageMock.clear()
  sessionStorageMock.clear()
  vi.clearAllMocks()
})
