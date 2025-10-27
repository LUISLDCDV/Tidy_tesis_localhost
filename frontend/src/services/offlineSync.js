import firebaseService from './firebase'
import { useAuthStore } from '@/stores/auth'
import { Notify } from 'quasar'

class OfflineSyncService {
  constructor() {
    this.isOnline = navigator.onLine
    this.syncQueue = []
    this.isSyncing = false
    this.retryAttempts = 3
    this.retryDelay = 2000 // 2 segundos

    this.initializeEventListeners()
  }

  /**
   * Inicializar listeners de conexión
   */
  initializeEventListeners() {
    window.addEventListener('online', () => {
      this.isOnline = true
      console.log('🌐 Conexión restaurada, iniciando sincronización...')
      this.syncPendingChanges()
    })

    window.addEventListener('offline', () => {
      this.isOnline = false
      console.log('📴 Conexión perdida, modo offline activado')
    })
  }

  /**
   * Agregar cambio a la cola de sincronización
   */
  async queueChange(changeData) {
    const authStore = useAuthStore()
    const userId = authStore.user?.id

    if (!userId) {
      console.error('Usuario no autenticado, no se puede guardar cambio offline')
      return false
    }

    const change = {
      id: `offline_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      userId,
      timestamp: new Date(),
      synced: false,
      attempts: 0,
      ...changeData
    }

    try {
      // Guardar en localStorage para persistencia
      const storedChanges = this.getStoredChanges()
      storedChanges.push(change)
      localStorage.setItem('offline_changes', JSON.stringify(storedChanges))

      // Agregar a cola en memoria
      this.syncQueue.push(change)

      // Si hay conexión, intentar sincronizar inmediatamente
      if (this.isOnline) {
        this.syncPendingChanges()
      }

      console.log('📝 Cambio agregado a cola offline:', change)
      return true

    } catch (error) {
      console.error('Error guardando cambio offline:', error)
      return false
    }
  }

  /**
   * Obtener cambios almacenados localmente
   */
  getStoredChanges() {
    try {
      const stored = localStorage.getItem('offline_changes')
      return stored ? JSON.parse(stored) : []
    } catch (error) {
      console.error('Error leyendo cambios offline:', error)
      return []
    }
  }

  /**
   * Sincronizar cambios pendientes
   */
  async syncPendingChanges() {
    if (this.isSyncing || !this.isOnline) {
      return
    }

    this.isSyncing = true

    try {
      // Cargar cambios desde localStorage si la cola está vacía
      if (this.syncQueue.length === 0) {
        this.syncQueue = this.getStoredChanges().filter(change => !change.synced)
      }

      if (this.syncQueue.length === 0) {
        console.log('✅ No hay cambios pendientes para sincronizar')
        this.isSyncing = false
        return
      }

      console.log(`🔄 Sincronizando ${this.syncQueue.length} cambios pendientes...`)

      const syncPromises = this.syncQueue.map(change => this.syncSingleChange(change))
      await Promise.allSettled(syncPromises)

      // Limpiar cambios sincronizados
      this.cleanupSyncedChanges()

      // Mostrar notificación de éxito
      const syncedCount = this.syncQueue.filter(change => change.synced).length
      if (syncedCount > 0) {
        Notify.create({
          type: 'positive',
          message: `✅ ${syncedCount} cambios sincronizados correctamente`,
          timeout: 3000
        })
      }

    } catch (error) {
      console.error('Error durante sincronización:', error)
      Notify.create({
        type: 'negative',
        message: 'Error sincronizando cambios offline',
        timeout: 3000
      })
    } finally {
      this.isSyncing = false
    }
  }

  /**
   * Sincronizar un cambio individual
   */
  async syncSingleChange(change) {
    try {
      change.attempts = (change.attempts || 0) + 1

      console.log(`🔄 Sincronizando cambio: ${change.type} (intento ${change.attempts})`)

      let result = false

      switch (change.type) {
        case 'CREATE_ELEMENT':
          result = await this.syncCreateElement(change)
          break
        case 'UPDATE_ELEMENT':
          result = await this.syncUpdateElement(change)
          break
        case 'DELETE_ELEMENT':
          result = await this.syncDeleteElement(change)
          break
        case 'CREATE_MESSAGE':
          result = await this.syncCreateMessage(change)
          break
        case 'UPDATE_USER_PROFILE':
          result = await this.syncUpdateUserProfile(change)
          break
        default:
          console.warn('Tipo de cambio no reconocido:', change.type)
          result = false
      }

      if (result) {
        change.synced = true
        change.syncedAt = new Date()
        console.log(`✅ Cambio sincronizado: ${change.type}`)

        // Guardar en Firebase para historial
        if (firebaseService) {
          await firebaseService.markChangeSynced(change.userId, change.id)
        }
      } else {
        throw new Error('Sincronización falló')
      }

    } catch (error) {
      console.error(`❌ Error sincronizando cambio ${change.type}:`, error)

      // Si hemos alcanzado el máximo de intentos, marcar como error
      if (change.attempts >= this.retryAttempts) {
        change.syncError = error.message
        change.synced = false // No sincronizado pero no se reintenta
        console.error(`🚫 Cambio marcado como error tras ${change.attempts} intentos`)
      } else {
        // Reintentar más tarde con backoff exponencial
        const delay = this.retryDelay * Math.pow(2, change.attempts - 1)
        setTimeout(() => {
          if (this.isOnline) {
            this.syncSingleChange(change)
          }
        }, delay)
      }
    }
  }

  /**
   * Sincronizar creación de elemento
   */
  async syncCreateElement(change) {
    try {
      const api = (await import('./api')).default
      const response = await api.post('/elementos/saveUpdate', change.data)

      // Actualizar ID local con ID del servidor si es necesario
      if (response.data?.id && change.localId) {
        this.updateLocalElementId(change.localId, response.data.id)
      }

      return true
    } catch (error) {
      console.error('Error creando elemento:', error)
      return false
    }
  }

  /**
   * Sincronizar actualización de elemento
   */
  async syncUpdateElement(change) {
    try {
      const api = (await import('./api')).default
      await api.post('/elementos/saveUpdate', change.data)
      return true
    } catch (error) {
      console.error('Error actualizando elemento:', error)
      return false
    }
  }

  /**
   * Sincronizar eliminación de elemento
   */
  async syncDeleteElement(change) {
    try {
      const api = (await import('./api')).default
      await api.post(`/elementos/eliminarElemento/${change.data.id}`, {})
      return true
    } catch (error) {
      console.error('Error eliminando elemento:', error)
      return false
    }
  }

  /**
   * Sincronizar creación de mensaje
   */
  async syncCreateMessage(change) {
    try {
      if (firebaseService) {
        await firebaseService.addMessage(change.data.chatId, change.data.message)
        return true
      }
      return false
    } catch (error) {
      console.error('Error creando mensaje:', error)
      return false
    }
  }

  /**
   * Sincronizar actualización de perfil
   */
  async syncUpdateUserProfile(change) {
    try {
      const api = (await import('./api')).default
      await api.put('/usuarios/profile', change.data)
      return true
    } catch (error) {
      console.error('Error actualizando perfil:', error)
      return false
    }
  }

  /**
   * Actualizar ID local con ID del servidor
   */
  updateLocalElementId(localId, serverId) {
    // Aquí se podría actualizar el store/localStorage con el nuevo ID
    console.log(`🔄 Actualizando ID local ${localId} con ID servidor ${serverId}`)
  }

  /**
   * Limpiar cambios ya sincronizados
   */
  cleanupSyncedChanges() {
    // Filtrar cambios no sincronizados
    this.syncQueue = this.syncQueue.filter(change => !change.synced)

    // Actualizar localStorage
    const storedChanges = this.getStoredChanges()
    const pendingChanges = storedChanges.filter(change => !change.synced)
    localStorage.setItem('offline_changes', JSON.stringify(pendingChanges))

    console.log(`🧹 Limpieza completada. ${pendingChanges.length} cambios pendientes restantes`)
  }

  /**
   * Obtener estadísticas de sincronización
   */
  getSyncStats() {
    const storedChanges = this.getStoredChanges()
    const pending = storedChanges.filter(change => !change.synced && !change.syncError)
    const errors = storedChanges.filter(change => change.syncError)
    const synced = storedChanges.filter(change => change.synced)

    return {
      total: storedChanges.length,
      pending: pending.length,
      errors: errors.length,
      synced: synced.length,
      isOnline: this.isOnline,
      isSyncing: this.isSyncing
    }
  }

  /**
   * Forzar sincronización manual
   */
  async forcSync() {
    if (!this.isOnline) {
      Notify.create({
        type: 'warning',
        message: 'No hay conexión a internet',
        timeout: 3000
      })
      return false
    }

    await this.syncPendingChanges()
    return true
  }

  /**
   * Limpiar todo el historial offline
   */
  clearOfflineHistory() {
    this.syncQueue = []
    localStorage.removeItem('offline_changes')
    console.log('🗑️ Historial offline limpiado')
  }
}

// Exportar instancia singleton
const offlineSyncService = new OfflineSyncService()

export default offlineSyncService