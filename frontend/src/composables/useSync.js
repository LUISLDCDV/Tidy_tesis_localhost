import { ref, computed } from 'vue'
import { useOfflineStorage } from './useOfflineStorage'
import { useNetworkStatus } from './useNetworkStatus'
import { useQuasar } from 'quasar'

/**
 * Composable para manejo de sincronización entre local y servidor
 * Integra almacenamiento offline y estado de red para sincronización inteligente
 */

export function useSync() {
  const { 
    isOnline, 
    save: saveLocal, 
    getAll: getAllLocal, 
    get: getLocal,
    delete: deleteLocal,
    syncWhenOnline,
    getSyncQueue,
    STORES 
  } = useOfflineStorage()
  
  const { whenOnline, testConnectivity } = useNetworkStatus()
  const $q = useQuasar()
  
  // Estado de sincronización
  const isSyncing = ref(false)
  const lastSyncTime = ref(null)
  const syncErrors = ref([])
  const syncStats = ref({
    pending: 0,
    completed: 0,
    failed: 0
  })
  
  // Configuración de sincronización
  const syncConfig = {
    autoSync: true,
    syncInterval: 30000, // 30 segundos
    retryDelay: 5000, // 5 segundos
    maxRetries: 3,
    batchSize: 10
  }
  
  let syncInterval = null

  // === OPERACIONES CRUD CON SINCRONIZACIÓN ===

  async function create(type, data) {
    try {
      // Siempre guardar localmente primero
      const localData = await saveLocal(STORES[type], {
        ...data,
        id: data.id || generateTempId(),
        _pending: !isOnline.value,
        _operation: 'create'
      })
      
      // Si estamos online, intentar sincronizar inmediatamente
      if (isOnline.value) {
        await syncItem(type, 'create', localData)
      }
      
      return localData
    } catch (error) {
      console.error('Error creating item:', error)
      throw error
    }
  }

  async function update(type, data) {
    try {
      // Guardar localmente
      const localData = await saveLocal(STORES[type], {
        ...data,
        _pending: !isOnline.value,
        _operation: 'update'
      })
      
      // Si estamos online, sincronizar
      if (isOnline.value) {
        await syncItem(type, 'update', localData)
      }
      
      return localData
    } catch (error) {
      console.error('Error updating item:', error)
      throw error
    }
  }

  async function remove(type, id) {
    try {
      // Marcar como eliminado localmente
      await deleteLocal(STORES[type], id)
      
      // Si estamos online, sincronizar eliminación
      if (isOnline.value) {
        await syncItem(type, 'delete', { id })
      }
      
      return true
    } catch (error) {
      console.error('Error deleting item:', error)
      throw error
    }
  }

  async function getAll(type, forceRefresh = false) {
    try {
      // Primero intentar obtener datos locales
      const localData = await getAllLocal(STORES[type])

      // Si estamos online y (se fuerza refresh O no hay datos locales), obtener del servidor
      if (isOnline.value && (forceRefresh || localData.length === 0)) {
        await syncFromServer(type)
        // Obtener los datos actualizados después del sync
        return await getAllLocal(STORES[type])
      }

      // Devolver datos locales si los hay
      return localData
    } catch (error) {
      console.error('Error getting items:', error)
      // En caso de error, devolver datos locales
      return await getAllLocal(STORES[type])
    }
  }

  async function getById(type, id) {
    try {
      return await getLocal(STORES[type], id)
    } catch (error) {
      console.error('Error getting item by id:', error)
      throw error
    }
  }

  // === SINCRONIZACIÓN ===

  async function syncItem(type, operation, data) {
    if (!isOnline.value) return false
    
    try {
      let response
      
      switch (operation) {
        case 'create':
          response = await createOnServer(type, data)
          break
        case 'update':
          response = await updateOnServer(type, data)
          break
        case 'delete':
          response = await deleteOnServer(type, data)
          break
        default:
          throw new Error(`Unknown operation: ${operation}`)
      }
      
      // Actualizar datos locales con respuesta del servidor
      if (response && operation !== 'delete') {
        await saveLocal(STORES[type], {
          ...response,
          _pending: false,
          _operation: null
        })
      }
      
      return true
    } catch (error) {
      console.error('Error syncing item:', error)
      syncErrors.value.push({
        type,
        operation,
        data,
        error: error.message,
        timestamp: new Date().toISOString()
      })
      return false
    }
  }

  async function syncFromServer(type) {
    if (!isOnline.value) return false

    if (!getAuthToken()) {
      console.warn('Cannot sync from server without authentication token')
      return false
    }

    try {
      const serverData = await fetchFromServer(type)
      
      // Normalizar respuesta del servidor - puede venir como array o como objeto con claves
      let itemsArray = serverData
      
      // Si serverData es un objeto, intentar extraer el array correspondiente
      if (serverData && typeof serverData === 'object' && !Array.isArray(serverData)) {
        // Mapeo específico de claves conocidas del backend
        const typeKeyMap = {
          'elements': ['elementos', 'elements'],
          'notes': ['notas', 'notes'],
          'alarms': ['alarmas', 'alarms'],
          'calendars': ['calendarios', 'calendars'],
          'events': ['eventos', 'events'],
          'objectives': ['objetivos', 'objectives']
        }
        
        // Buscar la clave que contenga un array
        const possibleKeys = [...(typeKeyMap[type] || []), type, type.slice(0, -1), 'data', 'items']
        for (const key of possibleKeys) {
          if (serverData[key] && Array.isArray(serverData[key])) {
            itemsArray = serverData[key]
            break
          }
        }
        
        // Si no encontramos un array, tomar el primer array que encontremos
        if (!Array.isArray(itemsArray)) {
          itemsArray = Object.values(serverData).filter(Array.isArray)[0] || []
        }
      }
      
      // Asegurar que tenemos un array
      if (!Array.isArray(itemsArray)) {
        console.warn(`Server data for ${type} is not an array:`, serverData)
        return false
      }
      
      // Actualizar datos locales con datos del servidor
      for (const item of itemsArray) {
        await saveLocal(STORES[type], {
          ...item,
          _pending: false,
          _operation: null
        })
      }
      
      return true
    } catch (error) {
      console.error('Error syncing from server:', error)
      return false
    }
  }

  async function fullSync() {
    if (!isOnline.value) {
      console.warn('Cannot sync while offline')
      return false
    }

    if (!getAuthToken()) {
      console.warn('Cannot sync without authentication token')
      return false
    }

    isSyncing.value = true
    
    try {
      // Obtener cola de sincronización
      const queue = await getSyncQueue()
      syncStats.value.pending = queue.filter(item => item.status === 'pending').length
      
      // Sincronizar elementos pendientes
      for (const queueItem of queue) {
        if (queueItem.status === 'pending') {
          const success = await syncItem(
            queueItem.storeName.replace('s', ''), // elements -> element
            queueItem.operation,
            queueItem.data
          )
          
          if (success) {
            syncStats.value.completed++
          } else {
            syncStats.value.failed++
          }
        }
      }
      
      // Sincronizar datos del servidor
      const types = ['elements', 'notes', 'alarms', 'calendars', 'events', 'objectives']
      for (const type of types) {
        await syncFromServer(type)
      }
      
      lastSyncTime.value = new Date().toISOString()
      return true
    } catch (error) {
      console.error('Error during full sync:', error)
      return false
    } finally {
      isSyncing.value = false
    }
  }

  // === INTEGRACIÓN CON API ===

  // Helper function to get auth token from both storages (consistent with API interceptor)
  function getAuthToken() {
    return localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token')
  }

  async function createOnServer(type, data) {
    // Integración con el API existente
    const { guardarElemento } = await import('@/services/api')
    const token = getAuthToken()

    if (!token) throw new Error('No authentication token')
    
    // Mapear tipo a formato del API
    const apiType = mapTypeToApiType(type)
    const response = await guardarElemento(token, apiType, data)
    
    return response
  }

  async function updateOnServer(type, data) {
    const { guardarElemento } = await import('@/services/api')
    const token = getAuthToken()

    if (!token) throw new Error('No authentication token')
    
    const apiType = mapTypeToApiType(type)
    const response = await guardarElemento(token, apiType, data)
    
    return response
  }

  async function deleteOnServer(type, data) {
    const { eliminarElemento } = await import('@/services/api')
    const token = getAuthToken()

    if (!token) throw new Error('No authentication token')
    
    await eliminarElemento(token, data.id)
    return true
  }

  async function fetchFromServer(type) {
    const token = getAuthToken()
    if (!token) throw new Error('No authentication token')

    // Mapear a función del API correspondiente
    const apiFunctions = {
      elements: 'obtenerElementos',
      notes: 'obtenerNotas',
      alarms: 'obtenerAlarmas',
      calendars: 'obtenerCalendarios',
      events: 'obtenerEventos',
      objectives: 'obtenerObjetivos'
    }

    const functionName = apiFunctions[type]
    if (!functionName) throw new Error(`No API function for type: ${type}`)

    const apiModule = await import('@/services/api')
    const apiFunction = apiModule[functionName]

    if (!apiFunction) throw new Error(`API function not found: ${functionName}`)

    const response = await apiFunction(token)
    return response.data || response
  }

  // === UTILIDADES ===

  function mapTypeToApiType(type) {
    const mapping = {
      elements: 'elemento',
      notes: 'nota',
      alarms: 'alarma',
      calendars: 'calendario',
      events: 'evento',
      objectives: 'objetivo'
    }
    return mapping[type] || type
  }

  function generateTempId() {
    return `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
  }

  function startAutoSync() {
    if (syncInterval) return
    
    syncInterval = setInterval(() => {
      if (isOnline.value && !isSyncing.value) {
        syncWhenOnline()
      }
    }, syncConfig.syncInterval)
  }

  function stopAutoSync() {
    if (syncInterval) {
      clearInterval(syncInterval)
      syncInterval = null
    }
  }

  // === ESTADO COMPUTADO ===

  const syncStatus = computed(() => {
    if (isSyncing.value) return 'syncing'
    if (!isOnline.value) return 'offline'
    if (syncErrors.value.length > 0) return 'error'
    return 'synced'
  })

  const hasPendingChanges = computed(() => {
    return syncStats.value.pending > 0
  })

  // Inicializar auto-sync cuando venga la conexión
  whenOnline(() => {
    if (syncConfig.autoSync && getAuthToken()) {
      startAutoSync()
      fullSync()
    }
  })

  return {
    // Estado reactivo
    isOnline,
    isSyncing,
    lastSyncTime,
    syncErrors,
    syncStats,
    syncStatus,
    hasPendingChanges,
    
    // Operaciones CRUD
    create,
    update,
    remove,
    getAll,
    getById,
    
    // Sincronización
    fullSync,
    syncFromServer,
    startAutoSync,
    stopAutoSync,
    
    // Configuración
    syncConfig,
    
    // Constantes
    STORES
  }
}

export default useSync