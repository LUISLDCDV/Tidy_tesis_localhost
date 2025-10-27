import { ref, reactive } from 'vue'

/**
 * Composable para manejo de almacenamiento offline con IndexedDB
 * Proporciona cache local, sincronización diferida y estado offline/online
 */

const DB_NAME = 'TidyOfflineDB'
const DB_VERSION = 1
const STORES = {
  elements: 'elements',
  notes: 'notes',
  alarms: 'alarms',
  calendars: 'calendars',
  events: 'events',
  objectives: 'objectives',
  syncQueue: 'syncQueue',
  userConfig: 'userConfig'
}

class OfflineStorage {
  constructor() {
    this.db = null
    this.isOnline = ref(navigator.onLine)
    this.syncQueue = reactive([])
    this.lastSync = ref(null)
    this.isSyncing = ref(false)
    
    this.initDB()
    this.setupNetworkListeners()
  }

  async initDB() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(DB_NAME, DB_VERSION)
      
      request.onerror = () => reject(request.error)
      request.onsuccess = () => {
        this.db = request.result
        resolve(this.db)
      }
      
      request.onupgradeneeded = (event) => {
        const db = event.target.result
        
        // Store para elementos
        if (!db.objectStoreNames.contains(STORES.elements)) {
          const elementsStore = db.createObjectStore(STORES.elements, { keyPath: 'id' })
          elementsStore.createIndex('tipo', 'tipo', { unique: false })
          elementsStore.createIndex('updated_at', 'updated_at', { unique: false })
        }
        
        // Store para notas
        if (!db.objectStoreNames.contains(STORES.notes)) {
          const notesStore = db.createObjectStore(STORES.notes, { keyPath: 'id' })
          notesStore.createIndex('elemento_id', 'elemento_id', { unique: false })
          notesStore.createIndex('tipo_nota_id', 'tipo_nota_id', { unique: false })
        }
        
        // Store para alarmas
        if (!db.objectStoreNames.contains(STORES.alarms)) {
          const alarmsStore = db.createObjectStore(STORES.alarms, { keyPath: 'id' })
          alarmsStore.createIndex('elemento_id', 'elemento_id', { unique: false })
        }
        
        // Store para calendarios
        if (!db.objectStoreNames.contains(STORES.calendars)) {
          const calendarsStore = db.createObjectStore(STORES.calendars, { keyPath: 'id' })
          calendarsStore.createIndex('elemento_id', 'elemento_id', { unique: false })
        }
        
        // Store para eventos
        if (!db.objectStoreNames.contains(STORES.events)) {
          const eventsStore = db.createObjectStore(STORES.events, { keyPath: 'id' })
          eventsStore.createIndex('calendario_id', 'calendario_id', { unique: false })
          eventsStore.createIndex('elemento_id', 'elemento_id', { unique: false })
        }
        
        // Store para objetivos
        if (!db.objectStoreNames.contains(STORES.objectives)) {
          const objectivesStore = db.createObjectStore(STORES.objectives, { keyPath: 'id' })
          objectivesStore.createIndex('elemento_id', 'elemento_id', { unique: false })
        }
        
        // Store para cola de sincronización
        if (!db.objectStoreNames.contains(STORES.syncQueue)) {
          const syncStore = db.createObjectStore(STORES.syncQueue, { keyPath: 'id', autoIncrement: true })
          syncStore.createIndex('timestamp', 'timestamp', { unique: false })
          syncStore.createIndex('type', 'type', { unique: false })
        }
        
        // Store para configuración de usuario
        if (!db.objectStoreNames.contains(STORES.userConfig)) {
          db.createObjectStore(STORES.userConfig, { keyPath: 'key' })
        }
      }
    })
  }

  setupNetworkListeners() {
    window.addEventListener('online', () => {
      this.isOnline.value = true
      this.syncWhenOnline()
    })
    
    window.addEventListener('offline', () => {
      this.isOnline.value = false
    })
  }

  // === OPERACIONES CRUD LOCALES ===

  async save(storeName, data) {
    if (!this.db) await this.initDB()
    
    const transaction = this.db.transaction([storeName], 'readwrite')
    const store = transaction.objectStore(storeName)
    
    // Agregar timestamp de última actualización
    const dataWithTimestamp = {
      ...data,
      updated_at: new Date().toISOString(),
      _localOnly: !this.isOnline.value // Marcar si fue creado offline
    }
    
    await store.put(dataWithTimestamp)
    
    // Si estamos offline, agregar a cola de sincronización
    if (!this.isOnline.value) {
      await this.addToSyncQueue('save', storeName, dataWithTimestamp)
    }
    
    return dataWithTimestamp
  }

  async get(storeName, id) {
    if (!this.db) await this.initDB()
    
    const transaction = this.db.transaction([storeName], 'readonly')
    const store = transaction.objectStore(storeName)
    
    return new Promise((resolve, reject) => {
      const request = store.get(id)
      request.onsuccess = () => resolve(request.result)
      request.onerror = () => reject(request.error)
    })
  }

  async getAll(storeName, indexName = null, indexValue = null) {
    if (!this.db) await this.initDB()
    
    const transaction = this.db.transaction([storeName], 'readonly')
    const store = transaction.objectStore(storeName)
    
    return new Promise((resolve, reject) => {
      let request
      
      if (indexName && indexValue !== null) {
        const index = store.index(indexName)
        request = index.getAll(indexValue)
      } else {
        request = store.getAll()
      }
      
      request.onsuccess = () => resolve(request.result)
      request.onerror = () => reject(request.error)
    })
  }

  async delete(storeName, id) {
    if (!this.db) await this.initDB()
    
    const transaction = this.db.transaction([storeName], 'readwrite')
    const store = transaction.objectStore(storeName)
    
    await store.delete(id)
    
    // Si estamos offline, agregar a cola de sincronización
    if (!this.isOnline.value) {
      await this.addToSyncQueue('delete', storeName, { id })
    }
  }

  async clear(storeName) {
    if (!this.db) await this.initDB()
    
    const transaction = this.db.transaction([storeName], 'readwrite')
    const store = transaction.objectStore(storeName)
    
    await store.clear()
  }

  // === SISTEMA DE SINCRONIZACIÓN ===

  async addToSyncQueue(operation, storeName, data) {
    const queueItem = {
      operation,
      storeName,
      data,
      timestamp: new Date().toISOString(),
      retries: 0,
      status: 'pending'
    }
    
    const transaction = this.db.transaction([STORES.syncQueue], 'readwrite')
    const store = transaction.objectStore(STORES.syncQueue)
    
    await store.add(queueItem)
    this.syncQueue.push(queueItem)
  }

  async getSyncQueue() {
    const queue = await this.getAll(STORES.syncQueue)
    this.syncQueue.splice(0, this.syncQueue.length, ...queue)
    return queue
  }

  async syncWhenOnline() {
    if (!this.isOnline.value || this.isSyncing.value) return
    
    this.isSyncing.value = true
    
    try {
      const queue = await this.getSyncQueue()
      
      for (const item of queue) {
        if (item.status === 'completed') continue
        
        try {
          await this.syncItem(item)
          await this.markItemAsCompleted(item.id)
        } catch (error) {
          console.error('Error syncing item:', error)
          await this.incrementRetries(item.id)
        }
      }
      
      this.lastSync.value = new Date().toISOString()
      await this.saveConfig('lastSync', this.lastSync.value)
      
    } catch (error) {
      console.error('Error during sync:', error)
    } finally {
      this.isSyncing.value = false
    }
  }

  async syncItem(item) {
    // Esta función debe ser implementada con la lógica específica del API
    // Por ahora es un placeholder
    const { operation, storeName, data } = item
    
    switch (operation) {
      case 'save':
        // Llamar API para guardar
        console.log('Syncing save:', storeName, data)
        break
      case 'delete':
        // Llamar API para eliminar
        console.log('Syncing delete:', storeName, data)
        break
      default:
        throw new Error(`Unknown operation: ${operation}`)
    }
  }

  async markItemAsCompleted(queueItemId) {
    const transaction = this.db.transaction([STORES.syncQueue], 'readwrite')
    const store = transaction.objectStore(STORES.syncQueue)
    
    const item = await store.get(queueItemId)
    if (item) {
      item.status = 'completed'
      await store.put(item)
    }
  }

  async incrementRetries(queueItemId) {
    const transaction = this.db.transaction([STORES.syncQueue], 'readwrite')
    const store = transaction.objectStore(STORES.syncQueue)
    
    const item = await store.get(queueItemId)
    if (item) {
      item.retries = (item.retries || 0) + 1
      if (item.retries > 3) {
        item.status = 'failed'
      }
      await store.put(item)
    }
  }

  // === CONFIGURACIÓN ===

  async saveConfig(key, value) {
    if (!this.db) await this.initDB()
    
    const transaction = this.db.transaction([STORES.userConfig], 'readwrite')
    const store = transaction.objectStore(STORES.userConfig)
    
    await store.put({ key, value, updated_at: new Date().toISOString() })
  }

  async getConfig(key) {
    if (!this.db) await this.initDB()
    
    const transaction = this.db.transaction([STORES.userConfig], 'readonly')
    const store = transaction.objectStore(STORES.userConfig)
    
    return new Promise((resolve, reject) => {
      const request = store.get(key)
      request.onsuccess = () => resolve(request.result?.value)
      request.onerror = () => reject(request.error)
    })
  }

  // === UTILIDADES ===

  async getStorageInfo() {
    if (!this.db) await this.initDB()
    
    const info = {}
    
    for (const storeName of Object.values(STORES)) {
      const count = await this.getCount(storeName)
      info[storeName] = count
    }
    
    return info
  }

  async getCount(storeName) {
    if (!this.db) await this.initDB()
    
    const transaction = this.db.transaction([storeName], 'readonly')
    const store = transaction.objectStore(storeName)
    
    return new Promise((resolve, reject) => {
      const request = store.count()
      request.onsuccess = () => resolve(request.result)
      request.onerror = () => reject(request.error)
    })
  }

  async clearAllData() {
    if (!this.db) await this.initDB()
    
    for (const storeName of Object.values(STORES)) {
      await this.clear(storeName)
    }
  }
}

// Instancia singleton
const offlineStorage = new OfflineStorage()

export function useOfflineStorage() {
  return {
    // Propiedades reactivas
    isOnline: offlineStorage.isOnline,
    syncQueue: offlineStorage.syncQueue,
    lastSync: offlineStorage.lastSync,
    isSyncing: offlineStorage.isSyncing,
    
    // Métodos CRUD
    save: offlineStorage.save.bind(offlineStorage),
    get: offlineStorage.get.bind(offlineStorage),
    getAll: offlineStorage.getAll.bind(offlineStorage),
    delete: offlineStorage.delete.bind(offlineStorage),
    clear: offlineStorage.clear.bind(offlineStorage),
    
    // Métodos de sincronización
    syncWhenOnline: offlineStorage.syncWhenOnline.bind(offlineStorage),
    getSyncQueue: offlineStorage.getSyncQueue.bind(offlineStorage),
    
    // Configuración
    saveConfig: offlineStorage.saveConfig.bind(offlineStorage),
    getConfig: offlineStorage.getConfig.bind(offlineStorage),
    
    // Utilidades
    getStorageInfo: offlineStorage.getStorageInfo.bind(offlineStorage),
    clearAllData: offlineStorage.clearAllData.bind(offlineStorage),
    
    // Constantes
    STORES
  }
}

export default useOfflineStorage